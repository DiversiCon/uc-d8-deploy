(window.webpackJsonp=window.webpackJsonp||[]).push([[9],{P8gK:function(e,t,n){"use strict";n.r(t);var o={name:"UcDropdown",props:{name:String,displayName:String,options:Array,onSelect:Function,defaultSelection:{type:String,default:"All"}},data:function(){return{selectedOption:this.defaultSelection,expanded:!1,itemID:Math.floor(1e3*Math.random()),optionContainer:null,ready:!1,buttonWidth:"100%"}},watch:{options:function(){var e=this;this.$nextTick(function(){e.getButtonWidth()})},expanded:function(){var e=document.getElementById("app");this.expanded?(e.addEventListener("click",this.toggleBlur,!0),this.$el.style.zIndex=60):(e.removeEventListener("click",this.toggleBlur,!0),this.$el.style.zIndex="inherit")}},mounted:function(){var e=this;window.addEventListener("load",function(){setTimeout(function(){e.$route.query[e.name]&&e.selectFromQuery(e.$route.query[e.name]),e.ready=!0},100)}),window.addEventListener("resize",this.debounce(200,e.getButtonWidth)),e.$parent.$on("resetTypes",function(){"type"===e.name&&(e.selectedOption="All",e.onSelect("All"))})},methods:{getSelected:function(){return"All"===this.selectedOption?this.displayName:this.selectedOption},formatName:function(e){return e.toLowerCase().replace(/\s/g,"")},getValue:function(e){return e.value?e.value:e.name},toggle:function(){this.expanded=!this.expanded},toggleBlur:function(e){e.target.id!=="c-dropdown-".concat(this.itemID)&&this.toggle()},close:function(){this.$el.querySelector("button").focus(),this.expanded=!1},nextItem:function(){var e=document.activeElement.nextSibling;e&&e.focus()},prevItem:function(){var e=document.activeElement.previousSibling;e&&e.focus()},handleSelect:function(e){var t=!(1<arguments.length&&void 0!==arguments[1])||arguments[1],n=e.value?e.value:e.name;this.expanded=!1,this.$el.querySelector("button").focus(),this.selectedOption=e.name,this.$el.querySelector(".c-dropdown__list").setAttribute("aria-activedescendant","option-".concat(this.formatName(e.name))),t&&this.updateQuery(this.name,n),this.onSelect(n)},updateQuery:function(e,t){var n=Object.assign({},this.$route.query);n[e]=t,this.$router.replace({query:n})},selectFromQuery:function(e){this.selectedOption=e,this.$el.querySelector(".c-dropdown__list").setAttribute("aria-activedescendant","option-".concat(this.formatName(e)))},reset:function(){this.selectFromQuery("All")},getButtonWidth:function(){var e=this,t=this.$refs.list;768<=window.innerWidth?(t.style.position="relative",setTimeout(function(){var n=t.offsetWidth+1;e.buttonWidth="".concat(n,"px"),t.style.width="".concat(n,"px"),t.style.position="absolute"},50)):(this.buttonWidth="100%",t.style.width="100%")}}},i=n("KHd+"),a=Object(i.a)(o,function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{staticClass:"c-dropdown",class:"c-dropdown--"+e.name},[n("div",{staticClass:"c-dropdown__wrap"},[n("button",{directives:[{name:"show",rawName:"v-show",value:e.ready,expression:"ready"}],ref:"button",staticClass:"c-dropdown__button",class:{expanded:e.expanded},style:"width: "+e.buttonWidth,attrs:{id:"c-dropdown-"+e.itemID,"aria-haspopup":"listbox","aria-expanded":""+e.expanded},on:{click:function(t){return t.preventDefault(),e.toggle(t)},keyup:[function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"esc",27,t.key,["Esc","Escape"])?null:e.toggle(t)},function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"down",40,t.key,["Down","ArrowDown"])?null:(t.preventDefault(),e.toggle(t))}]}},[e._v("\n      "+e._s(e.getSelected()))]),e._v(" "),n("ul",{ref:"list",staticClass:"c-dropdown__list",class:{expanded:e.expanded},attrs:{"aria-labelledby":"c-dropdown-"+e.itemID,role:"listbox"},on:{keyup:[function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"esc",27,t.key,["Esc","Escape"])?null:(t.preventDefault(),e.close(t))},function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"down",40,t.key,["Down","ArrowDown"])?null:(t.preventDefault(),e.nextItem(t))},function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"up",38,t.key,["Up","ArrowUp"])?null:(t.preventDefault(),e.prevItem(t))}]}},e._l(e.options,function(t){return n("li",{key:"option-"+e.formatName(t.name),attrs:{id:"option-"+e.formatName(t.name),"aria-selected":e.selectedOption===t.name,value:e.getValue(t),tabindex:"0",role:"option"},on:{keyup:function(n){return!n.type.indexOf("key")&&e._k(n.keyCode,"enter",13,n.key,"Enter")&&e._k(n.keyCode,"space",32,n.key,[" ","Spacebar"])?null:(n.preventDefault(),e.handleSelect(t))},click:function(n){return n.preventDefault(),e.handleSelect(t)}}},[e._v("\n        "+e._s(t.name)+"\n      ")])}),0)])])},[],!1,null,null,null);t.default=a.exports}}]);