(window.webpackJsonp=window.webpackJsonp||[]).push([[33],{nP7s:function(t,i,n){"use strict";n.r(i);var e={name:"TintEmbed",props:{tintId:String,query:String,columns:{type:Number,default:4},rows:Number,personalizationId:Number},data:function(){return{columnCount:this.columns,count:3,isMobile:!0,isReady:!1}},mounted:function(){this.init()},methods:{init:function(){this.viewportDetect(),window.addEventListener("load",this.renderTint())},onResize:function(){var t=this;this.viewportDetect(),this.isReady=!1,this.$nextTick(function(){t.isReady=!0,HM.render()})},viewportDetect:function(){this.isMobile=768>window.innerWidth,this.getCounts()},getCounts:function(){var t=this.isMobile?3:8,i=this.isMobile?1:this.columns;this.rows&&!this.isMobile?this.count=this.columns?this.rows*this.columns:this.rows*t:(this.count=t,this.columnCount=i)},renderTint:function(){var t=this;this.isReady=!0,this.$nextTick(function(){t.$emit("embed-ready")})}}},s=n("KHd+"),o=Object(s.a)(e,function(){var t=this,i=t.$createElement,n=t._self._c||i;return n("div",{staticClass:"c-tint-embed"},[t.isReady?n("div",[n("div",{staticClass:"tintup",staticStyle:{height:"500px",width:"100%"},attrs:{"data-id":t.tintId,"data-columns":t.columnCount,"data-count":t.count,"data-query":t.query,"data-personalization-id":t.personalizationId,"data-expand":"true","data-paginate":"true"}})]):t._e()])},[],!1,null,null,null);i.default=o.exports}}]);