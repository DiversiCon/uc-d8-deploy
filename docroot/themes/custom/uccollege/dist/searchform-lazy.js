(window.webpackJsonp=window.webpackJsonp||[]).push([[26],{HTww:function(e,t,r){"use strict";r.r(t);var a={name:"UcSearchform",components:{UcIcon:function(){return Promise.resolve().then(r.bind(null,"qeU3"))}},props:{theme:String,placeholder:{type:String,default:"Search"}},data:function(){return{query:this.query}},computed:{getTheme:function(){return this.theme?"c-searchform--".concat(this.theme):""}},mounted:function(){this.query=this.$route.query.query},methods:{}},c=r("KHd+"),n=Object(c.a)(a,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"c-searchform",class:e.getTheme},[r("form",{attrs:{action:"/search",method:"get",autocomplete:"off","accept-charset":"UTF-8"}},[r("div",{staticClass:"c-searchform__input_container"},[r("input",{attrs:{placeholder:e.placeholder,"aria-label":e.placeholder,type:"text",name:"query"},domProps:{value:e.query}}),e._v(" "),r("button",{attrs:{type:"submit",role:"button","aria-label":"search"}},[r("uc-icon",{attrs:{glyph:"search"}})],1)])])])},[],!1,null,null,null);t.default=n.exports}}]);