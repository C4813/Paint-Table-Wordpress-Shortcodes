jQuery(function($) {

    // ---------- L10n helper ----------

    function pctL10n(key, fallback) {
        if (window.pctShadeHelperL10n && typeof window.pctShadeHelperL10n[key] === 'string') {
            return window.pctShadeHelperL10n[key];
        }
        return fallback;
    }
    
    // ---------- Colour helpers (shared via pct-color-utils.js) ----------
    
    var hexToRgb        = window.pctColorUtils.hexToRgb;
    var rgbToHex        = window.pctColorUtils.rgbToHex;
    var mixColors       = window.pctColorUtils.mixColors;
    var textColorForHex = window.pctColorUtils.textColorForHex;

    // ---------- Shared dropdown helpers (shade helper) ----------

    function closeAllDropdowns() {
        $('.pct-mix-dropdown, .pct-mix-range-dropdown').each(function() {
            var $dd = $(this);
            $dd.removeClass('pct-mix-open');
            $dd.find('.pct-mix-list')
               .attr('hidden', 'hidden');
        });
    }

    $(document).on('click', function() {
        closeAllDropdowns();
    });

    function filterPaintOptions($column, rangeId) {
        var $dropdown = $column.find('.pct-mix-dropdown');
        var $list     = $dropdown.find('.pct-mix-list');
        var $options  = $list.find('.pct-mix-option');

        if (!rangeId) {
            // All ranges
            $options.show();
        } else {
            $options.each(function() {
                var $opt      = $(this);
                var optRange  = String($opt.data('range') || '');
                var shouldShow = (optRange === String(rangeId));
                $opt.toggle(shouldShow);
            });
        }

        // Reset current paint selection when range changes
        $dropdown.find('.pct-mix-value').val('');
        $dropdown.attr('data-hex', '');
        $list.find('.pct-mix-option').removeClass('is-selected');
        $dropdown.find('.pct-mix-trigger-label').text(
            pctL10n('selectPaint', 'Select a paint')
        );
        $dropdown.find('.pct-mix-trigger-swatch').css('background-color', 'transparent');
    }

    // ---------- Shade helper dropdowns ----------

    function initPaintDropdown($dropdown) {
        var $trigger = $dropdown.find('.pct-mix-trigger');
        var $list    = $dropdown.find('.pct-mix-list');
        var $hidden  = $dropdown.find('.pct-mix-value');
        var $label   = $dropdown.find('.pct-mix-trigger-label');
        var $swatch  = $dropdown.find('.pct-mix-trigger-swatch');

        // Open/close list
        $trigger.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var isOpen = $dropdown.hasClass('pct-mix-open');
            closeAllDropdowns();
            if (!isOpen) {
                $dropdown.addClass('pct-mix-open');
                $list.removeAttr('hidden');
            }
        });

        // Select an option
        $list.on('click', '.pct-mix-option:visible', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $opt  = $(this);
            var hex   = $opt.data('hex') || '';
            var label = $opt.data('label') || '';

            $list.find('.pct-mix-option').removeClass('is-selected');
            $opt.addClass('is-selected');

            $hidden.val(hex);
            $dropdown.attr('data-hex', hex);

            if (label) {
                $label.text(label);
            }
            if ($swatch.length) {
                if (hex) {
                    $swatch.css('background-color', hex);
                } else {
                    $swatch.css('background-color', 'transparent');
                }
            }

            closeAllDropdowns();

            var $shadeContainer = $dropdown.closest('.pct-shade-container');
            if ($shadeContainer.length) {
                updateShadeScale($shadeContainer);
            }
        });
    }

    function initRangeDropdown($dropdown) {
        var $trigger = $dropdown.find('.pct-mix-trigger');
        var $list    = $dropdown.find('.pct-mix-list');
        var $hidden  = $dropdown.find('.pct-mix-range-value');
        var $label   = $dropdown.find('.pct-mix-trigger-label');

        // Open/close list
        $trigger.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var isOpen = $dropdown.hasClass('pct-mix-open');
            closeAllDropdowns();
            if (!isOpen) {
                $dropdown.addClass('pct-mix-open');
                $list.removeAttr('hidden');
            }
        });

        // Select a range
        $list.on('click', '.pct-mix-range-option', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $opt    = $(this);
            var rangeId = $opt.data('range'); // may be "" for All
            var label   = $opt.find('.pct-mix-option-label').text() || '';

            $list.find('.pct-mix-range-option').removeClass('is-selected');
            $opt.addClass('is-selected');

            $hidden.val(rangeId);
            if (label) {
                $label.text(label);
            }

            closeAllDropdowns();

            // Filter paints in this column based on range
            var $column = $dropdown.closest('.pct-mix-column');
            filterPaintOptions($column, rangeId);

            var $shadeContainer = $dropdown.closest('.pct-shade-container');
            if ($shadeContainer.length) {
                updateShadeScale($shadeContainer);
            }
        });
    }

    // ---------- Shade range helper logic ----------

    function updateShadeScale($container) {
        var $shadeHelper = $container.find('.pct-shade-helper');
        if (!$shadeHelper.length) {
            return;
        }

        var $shadeColumn = $shadeHelper.find('.pct-mix-column-shade');
        var $scale       = $shadeHelper.find('.pct-shade-scale');
        if (!$shadeColumn.length || !$scale.length) {
            return;
        }

        var $paintDropdown = $shadeColumn.find('.pct-mix-dropdown-shade');
        var baseHex        = $paintDropdown.find('.pct-mix-value').val() || '';

        if (!baseHex) {
            $scale.html(
                '<p class="pct-shade-empty">' +
                    pctL10n('selectPaint', 'Select a paint to see lighter and darker mixes.') +
                '</p>'
            );
            return;
        }

        var baseRgb = hexToRgb(baseHex);
        if (!baseRgb) {
            $scale.html(
                '<p class="pct-shade-empty">' +
                    pctL10n('invalidHex', 'This colour has an invalid hex value.') +
                '</p>'
            );
            return;
        }

        // find the selected option for this base colour
        var $selectedOption = $paintDropdown.find('.pct-mix-option.is-selected').first();
        if (!$selectedOption.length) {
            // Fallback: match by hex
            $paintDropdown.find('.pct-mix-option').each(function () {
                var $opt = $(this);
                var optHex = ($opt.data('hex') || '').toString().toLowerCase();
                if (optHex === baseHex.toString().toLowerCase()) {
                    $selectedOption = $opt;
                    return false; // break
                }
            });
        }

        if (!$selectedOption.length) {
            $scale.html(
                '<p class="pct-shade-empty">' +
                    pctL10n('noSelectedPaint', 'Could not determine the selected paint in this range.') +
                '</p>'
            );
            return;
        }

        var baseRangeId = $selectedOption.data('range');
        if (!baseRangeId && baseRangeId !== 0) {
            $scale.html(
                '<p class="pct-shade-empty">' +
                    pctL10n('noRange', 'This paint is not assigned to a range.') +
                '</p>'
            );
            return;
        }

        var baseLabel = $selectedOption.data('label') || '';

        var darkest = null;
        var lightest = null;

        // Collect paints in the same range and find darkest/lightest anchors
        $paintDropdown.find('.pct-mix-option').each(function () {
            var $opt  = $(this);
            var optHex = $opt.data('hex') || '';
            var optRange = $opt.data('range');

            if (!optHex || String(optRange) !== String(baseRangeId)) {
                return;
            }

            // Skip the base colour itself when choosing anchors
            if (optHex.toString().toLowerCase() === baseHex.toString().toLowerCase()) {
                return;
            }

            var rgb = hexToRgb(optHex);
            if (!rgb) {
                return;
            }

            var lum = (0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b) / 255;
            var label = $opt.data('label') || '';

            if (!darkest || lum < darkest.lum) {
                darkest = { hex: optHex, lum: lum, label: label };
            }
            if (!lightest || lum > lightest.lum) {
                lightest = { hex: optHex, lum: lum, label: label };
            }
        });

        if (!darkest && !lightest) {
            $scale.html(
                '<p class="pct-shade-empty">' +
                    pctL10n('notEnoughPaints', 'Not enough paints in this range to build a shade ladder.') +
                '</p>'
            );
            return;
        }

        // Ratios: [partsBase, partsOther]
        // Darkest at the top (more darkener), then approach base
        var darkerRatios = [
            [1, 3], // darkest: 1 base, 3 darkener
            [1, 1],
            [3, 1]  // closest to base: 3 base, 1 darkener
        ];
        // For lighter mixes: closest to base first after base, then lightest
        var lighterRatios = [
            [3, 1], // closest to base: 3 base, 1 lightener
            [1, 1],
            [1, 3]  // lightest: 1 base, 3 lightener
        ];

        var rows = [];

        if (darkest) {
            darkerRatios.forEach(function (pair) {
                var mixed = mixColors(baseHex, darkest.hex, pair[0], pair[1]);
                if (!mixed) {
                    return;
                }
                rows.push({
                    type: 'darker',
                    ratio: pair,
                    hex: mixed.toUpperCase(),
                    otherLabel: darkest.label || ''
                });
            });
        }

        // Base in the centre
        rows.push({
            type: 'base',
            ratio: null,
            hex: baseHex.toUpperCase(),
            baseLabel: baseLabel
        });

        if (lightest) {
            lighterRatios.forEach(function (pair) {
                var mixed = mixColors(baseHex, lightest.hex, pair[0], pair[1]);
                if (!mixed) {
                    return;
                }
                rows.push({
                    type: 'lighter',
                    ratio: pair,
                    hex: mixed.toUpperCase(),
                    otherLabel: lightest.label || ''
                });
            });
        }

        if (!rows.length) {
            $scale.html(
                '<p class="pct-shade-empty">' +
                    pctL10n('unableToGenerate', 'Unable to generate mixes for this colour.') +
                '</p>'
            );
            return;
        }

        var html = '';
        rows.forEach(function (row) {
            var mainText = '';
            var textColor = textColorForHex(row.hex);

            if (row.type === 'base') {
                // Centre row: just the base paint name
                mainText = row.baseLabel || '';
            } else {
                var otherLabel = row.otherLabel || '';
                var partsBase  = row.ratio ? row.ratio[0] : 0;
                var partsOther = row.ratio ? row.ratio[1] : 0;

                if (otherLabel && baseLabel && partsBase && partsOther) {
                    mainText = otherLabel + ' ' + partsOther + ' : ' + partsBase + ' ' + baseLabel;
                } else {
                    mainText = row.hex;
                }
            }

            html += '<div class="pct-shade-row pct-shade-row-' + row.type +
                    '" style="background-color:' + row.hex + ';color:' + textColor + ';">';
            html += '  <div class="pct-shade-meta">';
            html += '    <div class="pct-shade-ratio">' + mainText + '</div>';
            html += '    <div class="pct-shade-hex">' + row.hex + '</div>';
            html += '  </div>';
            html += '</div>';
        });

        $scale.html(html);
    }

    // ---------- Init shade helper dropdowns + default hex ----------

    // Init dropdowns inside shade helpers
    $('.pct-shade-container .pct-mix-dropdown').each(function() {
        initPaintDropdown($(this));
    });

    $('.pct-shade-container .pct-mix-range-dropdown').each(function() {
        initRangeDropdown($(this));
    });

    // Initialise shade helper with default paint (by ID if available, otherwise by hex)
    $('.pct-shade-container').each(function() {
        var $container = $(this);
        var defaultHex = ($container.data('default-shade-hex') || '').toString().trim();
        var defaultId  = parseInt($container.data('default-shade-id'), 10);
        if (!isFinite(defaultId)) {
            defaultId = 0;
        }
    
        // Always initialise the empty state at least once
        if (!defaultId && !defaultHex) {
            updateShadeScale($container);
            return;
        }
    
        // Normalise the hex (# + lowercase) if provided
        if (defaultHex) {
            if (defaultHex.charAt(0) !== '#') {
                defaultHex = '#' + defaultHex;
            }
            defaultHex = defaultHex.toLowerCase();
        }
    
        var $shadeColumn   = $container.find('.pct-mix-column-shade');
        if (!$shadeColumn.length) {
            updateShadeScale($container);
            return;
        }
    
        var $paintDropdown = $shadeColumn.find('.pct-mix-dropdown-shade');
        var $options       = $paintDropdown.find('.pct-mix-option');
        if (!$options.length) {
            updateShadeScale($container);
            return;
        }
    
        // Prefer matching paint option by ID, then fall back to hex
        var $match = null;
    
        if (defaultId) {
            $options.each(function () {
                var optId = parseInt($(this).data('id'), 10);
                if (!isFinite(optId)) {
                    optId = 0;
                }
                if (optId === defaultId) {
                    $match = $(this);
                    return false; // break
                }
            });
        }
    
        if (!$match && defaultHex) {
            $options.each(function () {
                var hex = ($(this).data('hex') || '').toString().toLowerCase();
                if (hex === defaultHex) {
                    $match = $(this);
                    return false; // break
                }
            });
        }
    
        if (!$match) {
            updateShadeScale($container);
            return;
        }
    
        // Set the range dropdown based on the matched option
        var rangeId        = $match.data('range') || '';
        var $rangeDropdown = $shadeColumn.find('.pct-mix-range-dropdown-shade');
    
        if ($rangeDropdown.length) {
            var $rangeList   = $rangeDropdown.find('.pct-mix-list');
            var $rangeHidden = $rangeDropdown.find('.pct-mix-range-value');
            var $rangeLabel  = $rangeDropdown.find('.pct-mix-trigger-label');
    
            $rangeList.find('.pct-mix-range-option').removeClass('is-selected');
    
            var selector = '.pct-mix-range-option';
            if (rangeId !== '') {
                selector += '[data-range="' + String(rangeId) + '"]';
            } else {
                selector += '[data-range=""]';
            }
    
            var $rangeOpt = $rangeList.find(selector).first();
            if ($rangeOpt.length) {
                $rangeOpt.addClass('is-selected');
                $rangeHidden.val(rangeId);
                var rangeText = $rangeOpt.find('.pct-mix-option-label').text() || '';
                if (rangeText) {
                    $rangeLabel.text(rangeText);
                }
    
                // Filter paints by range (same as user picking a range)
                filterPaintOptions($shadeColumn, rangeId);
    
                // Re-find the matching paint after filtering (still prefer ID)
                $options = $paintDropdown.find('.pct-mix-option');
                var newMatch = null;
    
                if (defaultId) {
                    $options.each(function () {
                        var optId = parseInt($(this).data('id'), 10);
                        if (!isFinite(optId)) {
                            optId = 0;
                        }
                        if (optId === defaultId) {
                            newMatch = $(this);
                            return false;
                        }
                    });
                }
    
                if (!newMatch && defaultHex) {
                    $options.each(function () {
                        var hex = ($(this).data('hex') || '').toString().toLowerCase();
                        if (hex === defaultHex) {
                            newMatch = $(this);
                            return false;
                        }
                    });
                }
    
                if (newMatch) {
                    $match = newMatch;
                }
            }
        }
    
        if ($match) {
            var hexVal   = ($match.data('hex') || defaultHex || '').toString();
            var label    = $match.data('label') || '';
            var $hidden  = $paintDropdown.find('.pct-mix-value');
            var $labelEl = $paintDropdown.find('.pct-mix-trigger-label');
            var $swatch  = $paintDropdown.find('.pct-mix-trigger-swatch');
    
            $paintDropdown.attr('data-hex', hexVal);
            $hidden.val(hexVal);
            $paintDropdown.find('.pct-mix-option').removeClass('is-selected');
            $match.addClass('is-selected');
    
            if (label) {
                $labelEl.text(label);
            }
            if ($swatch.length && hexVal) {
                $swatch.css('background-color', hexVal);
            }
        }
    
        // Finally, build the shade ladder for this colour
        updateShadeScale($container);
    });
});
