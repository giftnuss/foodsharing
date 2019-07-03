jQuery(function($){
        $.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
                closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
                prevText: '<zurück', prevStatus: 'letzten Monat zeigen',
                nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',
                currentText: 'heute', currentStatus: '',
                monthNames: ['Januar','Februar','März','April','Mai','Juni',
                'Juli','August','September','Oktober','November','Dezember'],
                monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
                'Jul','Aug','Sep','Okt','Nov','Dez'],
                monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
                weekHeader: 'Wo', weekStatus: 'Woche des Monats',
                dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
                dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
                dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
                dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
                dateFormat: 'dd.mm.yy', firstDay: 1, 
                initStatus: 'Wähle ein Datum', isRTL: false};
        $.datepicker.setDefaults($.datepicker.regional['de']);
});


/*!
	Autosize v1.18.4 - 2014-01-11
	Automatically adjust textarea height based on user input.
	(c) 2014 Jack Moore - http://www.jacklmoore.com/autosize
	license: http://www.opensource.org/licenses/mit-license.php
*/
!function(a){var b,c={className:"autosizejs",append:"",callback:!1,resizeDelay:10,placeholder:!0},d='<textarea tabindex="-1" style="position:absolute; top:-999px; left:0; right:auto; bottom:auto; border:0; padding: 0; -moz-box-sizing:content-box; -webkit-box-sizing:content-box; box-sizing:content-box; word-wrap:break-word; height:0 !important; min-height:0 !important; overflow:hidden; transition:none; -webkit-transition:none; -moz-transition:none;"/>',e=["fontFamily","fontSize","fontWeight","fontStyle","letterSpacing","textTransform","wordSpacing","textIndent"],f=a(d).data("autosize",!0)[0];f.style.lineHeight="99px","99px"===a(f).css("lineHeight")&&e.push("lineHeight"),f.style.lineHeight="",a.fn.autosize=function(d){return this.length?(d=a.extend({},c,d||{}),f.parentNode!==document.body&&a(document.body).append(f),this.each(function(){function c(){var b,c=window.getComputedStyle?window.getComputedStyle(m,null):!1;c?(b=m.getBoundingClientRect().width,0===b&&(b=parseInt(c.width,10)),a.each(["paddingLeft","paddingRight","borderLeftWidth","borderRightWidth"],function(a,d){b-=parseInt(c[d],10)})):b=Math.max(n.width(),0),f.style.width=b+"px"}function g(){var g={};if(b=m,f.className=d.className,j=parseInt(n.css("maxHeight"),10),a.each(e,function(a,b){g[b]=n.css(b)}),a(f).css(g),c(),window.chrome){var h=m.style.width;m.style.width="0px";{m.offsetWidth}m.style.width=h}}function h(){var e,h;b!==m?g():c(),f.value=!m.value&&d.placeholder?(a(m).attr("placeholder")||"")+d.append:m.value+d.append,f.style.overflowY=m.style.overflowY,h=parseInt(m.style.height,10),f.scrollTop=0,f.scrollTop=9e4,e=f.scrollTop,j&&e>j?(m.style.overflowY="scroll",e=j):(m.style.overflowY="hidden",k>e&&(e=k)),e+=o,h!==e&&(m.style.height=e+"px",p&&d.callback.call(m,m))}function i(){clearTimeout(l),l=setTimeout(function(){var a=n.width();a!==r&&(r=a,h())},parseInt(d.resizeDelay,10))}var j,k,l,m=this,n=a(m),o=0,p=a.isFunction(d.callback),q={height:m.style.height,overflow:m.style.overflow,overflowY:m.style.overflowY,wordWrap:m.style.wordWrap,resize:m.style.resize},r=n.width();n.data("autosize")||(n.data("autosize",!0),("border-box"===n.css("box-sizing")||"border-box"===n.css("-moz-box-sizing")||"border-box"===n.css("-webkit-box-sizing"))&&(o=n.outerHeight()-n.height()),k=Math.max(parseInt(n.css("minHeight"),10)-o||0,n.height()),n.css({overflow:"hidden",overflowY:"hidden",wordWrap:"break-word",resize:"none"===n.css("resize")||"vertical"===n.css("resize")?"none":"horizontal"}),"onpropertychange"in m?"oninput"in m?n.on("input.autosize keyup.autosize",h):n.on("propertychange.autosize",function(){"value"===event.propertyName&&h()}):n.on("input.autosize",h),d.resizeDelay!==!1&&a(window).on("resize.autosize",i),n.on("autosize.resize",h),n.on("autosize.resizeIncludeStyle",function(){b=null,h()}),n.on("autosize.destroy",function(){b=null,clearTimeout(l),a(window).off("resize",i),n.off("autosize").off(".autosize").css(q).removeData("autosize")}),h())})):this}}(window.jQuery||window.$);


/**
 * jquery.switchButton.js v1.0
 */

(function($) {

    $.widget("sylightsUI.switchButton", {

        options: {
            checked: undefined,			// State of the switch

            show_labels: true,			// Should we show the on and off labels?
            labels_placement: "both", 	// Position of the labels: "both", "left" or "right"
            on_label: "An",				// Text to be displayed when checked
            off_label: "Aus",			// Text to be displayed when unchecked

            width: 25,					// Width of the button in pixels
            height: 11,					// Height of the button in pixels
            button_width: 12,			// Width of the sliding part in pixels

            clear: true,				// Should we insert a div with style="clear: both;" after the switch button?
            clear_after: null,		    // Override the element after which the clearing div should be inserted (null > right after the button)
            on_callback: undefined,		//callback function that will be executed after going to on state
            off_callback: undefined		//callback function that will be executed after going to off state
        },

        _create: function() {
            // Init the switch from the checkbox if no state was specified on creation
            if (this.options.checked === undefined) {
                this.options.checked = this.element.prop("checked");
            }

            this._initLayout();
            this._initEvents();
        },

        _initLayout: function() {
            // Hide the receiver element
            this.element.hide();

            // Create our objects: two labels and the button
            this.off_label = $("<span>").addClass("switch-button-label");
            this.on_label = $("<span>").addClass("switch-button-label");

            this.button_bg = $("<div>").addClass("switch-button-background");
            this.button = $("<div>").addClass("switch-button-button");

            // Insert the objects into the DOM
            this.off_label.insertAfter(this.element);
            this.button_bg.insertAfter(this.off_label);
            this.on_label.insertAfter(this.button_bg);

            this.button_bg.append(this.button);

            // Insert a clearing element after the specified element if needed
            if(this.options.clear)
            {
                if (this.options.clear_after === null) {
                    this.options.clear_after = this.on_label;
                }
                $("<div>").css({
                    clear: "left"
                }).insertAfter(this.options.clear_after);
            }

            // Call refresh to update labels text and visibility
            this._refresh();

            // Init labels and switch state
            // This will animate all checked switches to the ON position when
            // loading... this is intentional!
            this.options.checked = !this.options.checked;
            this._toggleSwitch();
        },

        _refresh: function() {
            // Refresh labels display
            if (this.options.show_labels) {
                this.off_label.show();
                this.on_label.show();
            }
            else {
                this.off_label.hide();
                this.on_label.hide();
            }

            // Move labels around depending on labels_placement option
            switch(this.options.labels_placement) {
                case "both":
                {
                    // Don't move anything if labels are already in place
                    if(this.button_bg.prev() !== this.off_label || this.button_bg.next() !== this.on_label)
                    {
                        // Detach labels form DOM and place them correctly
                        this.off_label.detach();
                        this.on_label.detach();
                        this.off_label.insertBefore(this.button_bg);
                        this.on_label.insertAfter(this.button_bg);

                        // Update label classes
                        this.on_label.addClass(this.options.checked ? "on" : "off").removeClass(this.options.checked ? "off" : "on");
                        this.off_label.addClass(this.options.checked ? "off" : "on").removeClass(this.options.checked ? "on" : "off");

                    }
                    break;
                }

                case "left":
                {
                    // Don't move anything if labels are already in place
                    if(this.button_bg.prev() !== this.on_label || this.on_label.prev() !== this.off_label)
                    {
                        // Detach labels form DOM and place them correctly
                        this.off_label.detach();
                        this.on_label.detach();
                        this.off_label.insertBefore(this.button_bg);
                        this.on_label.insertBefore(this.button_bg);

                        // update label classes
                        this.on_label.addClass("on").removeClass("off");
                        this.off_label.addClass("off").removeClass("on");
                    }
                    break;
                }

                case "right":
                {
                    // Don't move anything if labels are already in place
                    if(this.button_bg.next() !== this.off_label || this.off_label.next() !== this.on_label)
                    {
                        // Detach labels form DOM and place them correctly
                        this.off_label.detach();
                        this.on_label.detach();
                        this.off_label.insertAfter(this.button_bg);
                        this.on_label.insertAfter(this.off_label);

                        // update label classes
                        this.on_label.addClass("on").removeClass("off");
                        this.off_label.addClass("off").removeClass("on");
                    }
                    break;
                }

            }

            // Refresh labels texts
            this.on_label.html(this.options.on_label);
            this.off_label.html(this.options.off_label);

            // Refresh button's dimensions
            this.button_bg.width(this.options.width);
            this.button_bg.height(this.options.height);
            this.button.width(this.options.button_width);
            this.button.height(this.options.height);
        },

        _initEvents: function() {
            var self = this;

            // Toggle switch when the switch is clicked
            this.button_bg.click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                self._toggleSwitch();
                return false;
            });
            this.button.click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                self._toggleSwitch();
                return false;
            });

            // Set switch value when clicking labels
            this.on_label.click(function(e) {
                if (self.options.checked && self.options.labels_placement === "both") {
                    return false;
                }

                self._toggleSwitch();
                return false;
            });

            this.off_label.click(function(e) {
                if (!self.options.checked && self.options.labels_placement === "both") {
                    return false;
                }

                self._toggleSwitch();
                return false;
            });

        },

        _setOption: function(key, value) {
            if (key === "checked") {
                this._setChecked(value);
                return;
            }

            this.options[key] = value;
            this._refresh();
        },

        _setChecked: function(value) {
            if (value === this.options.checked) {
                return;
            }

            this.options.checked = !value;
            this._toggleSwitch();
        },

        _toggleSwitch: function() {
            this.options.checked = !this.options.checked;
            var newLeft = "";
            if (this.options.checked) {
                // Update the underlying checkbox state
                this.element.prop("checked", true);
                this.element.change();

                var dLeft = this.options.width - this.options.button_width;
                newLeft = "+=" + dLeft;

                // Update labels states
                if(this.options.labels_placement == "both")
                {
                    this.off_label.removeClass("on").addClass("off");
                    this.on_label.removeClass("off").addClass("on");
                }
                else
                {
                    this.off_label.hide();
                    this.on_label.show();
                }
                this.button_bg.addClass("checked");
                //execute on state callback if its supplied
                if(typeof this.options.on_callback === 'function') this.options.on_callback.call(this);
            }
            else {
                // Update the underlying checkbox state
                this.element.prop("checked", false);
                this.element.change();
                newLeft = "-1px";

                // Update labels states
                if(this.options.labels_placement == "both")
                {
                    this.off_label.removeClass("off").addClass("on");
                    this.on_label.removeClass("on").addClass("off");
                }
                else
                {
                    this.off_label.show();
                    this.on_label.hide();
                }
                this.button_bg.removeClass("checked");
                //execute off state callback if its supplied
                if(typeof this.options.off_callback === 'function') this.options.off_callback.call(this);
            }
            // Animate the switch
            this.button.animate({ left: newLeft }, 250, "easeInOutCubic");
        }

    });

})(jQuery);
