(window.webpackJsonp=window.webpackJsonp||[]).push([[6],{HSGY:function(e,t,n){"use strict";function i(e,t){for(var n,i=0;i<t.length;i++)(n=t[i]).enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}n.r(t);var o=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e)}return function(e,t,n){t&&i(e.prototype,t),n&&i(e,n)}(e,null,[{key:"cleanString",value:function(e){return e=this.unescapeDoubleQuotes(e),e=this.unescapeSingleQuotes(e)}},{key:"unescapeSingleQuotes",value:function(e){return e.replace(/\\'/g,"'")}},{key:"unescapeDoubleQuotes",value:function(e){return e.replace(/\\"/g,'"')}}]),e}(),a={name:"uc-accordion",props:{title:String,subtitle:String,twoColumn:{type:Boolean,default:!1},tag:{type:String,default:"h3"},open:{type:Boolean,default:!1},mobileOnly:{type:Boolean,default:!1},insetImage:{type:Boolean,default:!1}},data:function(){return{expanded:!1,index:Math.floor(1e3*Math.random()),isMobile:window.innerWidth<this.$parent.collapsePoint,buttonText:null}},computed:{showAccordion:function(){return this.isMobile&&this.mobileOnly||!this.mobileOnly}},mounted:function(){var e=this,t=this;t.expanded=this.open,t.buttonText=t.getButtonText(),window.addEventListener("resize",this.debounce(200,function(){t.isMobile=window.innerWidth<e.$parent.collapsePoint})),this.$parent.$on("resetItems",function(t){e.reset(t)})},methods:{toggle:function(){(!(0<arguments.length&&void 0!==arguments[0])||arguments[0])&&this.$parent.single&&this.$parent.$emit("toggled",this.index),this.expanded=!this.expanded},close:function(){this.expanded&&this.toggle()},reset:function(e){(e!==this.index||"all"===this.index)&&this.expanded&&this.toggle(!1)},nextHeading:function(){var e=this.$el.nextElementSibling;if(e){var t=e.getElementsByTagName("button")[0];t&&t.focus()}},prevHeading:function(){var e=this.$el.previousElementSibling;if(e){var t=e.getElementsByTagName("button")[0];t&&t.focus()}},contentClass:function(){return{"c-accordion-group--2col":this.twoColumn,expanded:this.expanded}},getButtonText:function(){var e="";if(this.insetImage){var t=this.$el.querySelector(".c-list__item-image img");if(null!=t){var n=t.src;e='<img data-src="'.concat(n,'" alt="" class="lazyload">')}}var i=this.subtitle?"".concat(this.title," <span>").concat(this.subtitle,"</span>"):this.title;return i=o.cleanString(i),e+"<div>".concat(i,"</div>")}}},s=n("KHd+"),c=Object(s.a)(a,function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("li",{staticClass:"c-accordion__item",class:{"no-accordion":!this.showAccordion},on:{keyup:[function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"down",40,t.key,["Down","ArrowDown"])?null:(t.preventDefault(),e.nextHeading(t))},function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"up",38,t.key,["Up","ArrowUp"])?null:(t.preventDefault(),e.prevHeading(t))},function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"esc",27,t.key,["Esc","Escape"])?null:(t.preventDefault(),e.close(t))}]}},[e.showAccordion?n("div",{class:{"c-accordion__item--expanded":e.expanded,"c-accordion__item--hasInset":e.insetImage}},[n(e.tag,{tag:"component",staticClass:"c-accordion__title"},[n("button",{attrs:{"aria-controls":"accordion-"+e.index,"aria-expanded":""+e.expanded},domProps:{innerHTML:e._s(e.buttonText)},on:{click:function(t){return t.preventDefault(),e.toggle(t)}}}),e._v(" "),n("div",{staticClass:"c-accordion__toggle-icon",class:{isToggled:e.expanded},attrs:{"aria-hidden":"true"}})]),e._v(" "),n("div",{staticClass:"c-accordion__ctn",class:e.contentClass(),attrs:{"aria-hidden":!e.expanded,id:"accordion-"+e.index}},[e._t("default")],2)],1):n("div",[n(e.tag,{tag:"component",staticClass:"c-accordion__heading"},[e._v(e._s(e.title)+"\n    ")]),e._v(" "),e._t("default")],2)])},[],!1,null,null,null);t.default=c.exports}}]);