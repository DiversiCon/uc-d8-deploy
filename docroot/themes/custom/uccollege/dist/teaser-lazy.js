(window.webpackJsonp=window.webpackJsonp||[]).push([[32],{"+lPU":function(t,e,n){"use strict";n.r(e);var i=n("vDqi"),s=n.n(i),a={name:"UcTeaser",components:{UcIcon:function(){return Promise.resolve().then(n.bind(null,"qeU3"))},UcSlider:function(){return n.e(30).then(n.bind(null,"ueJB"))}},props:{heading:String,topic:String,topicUrl:String,datasource:{type:String,default:"/themes/custom/uccollege/components/teaser/news.json"},mobileslider:{type:Boolean,default:!1},mobileonly:{type:Boolean,default:!1}},data:function(){return{content:null}},mounted:function(){this.getAllData()},methods:{getAllData:function(){var t=this;s.a.get(t.datasource).then(function(e){t.content=e.data.items}).catch(function(t){console.log(t)}).then(function(){})},showIcon:function(t){return-1!==["podcast","video"].indexOf(t)}}},c=n("KHd+"),o=Object(c.a)(a,function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("section",{staticClass:"c-teaser l-stripe c-teaser--external",class:{"hide-for-medium":t.mobileonly}},[t.topic?n("h2",{staticClass:"t-heading--topic"},[n("a",{attrs:{href:t.topicUrl}},[t._v(t._s(t.topic))])]):t._e(),t._v(" "),t.heading?n("h3",{staticClass:"t-heading--medium"},[t._v(t._s(t.heading))]):t._e(),t._v(" "),n(t.mobileslider?"uc-slider":"div",{tag:"component"},[n("ul",t._l(t.content,function(e,i){return n("li",{key:"item-"+i},[n("div",{staticClass:"c-teaser__img"},[n("a",{attrs:{href:e.url}},[t.showIcon(e.type)?n("div",{staticClass:"c-media-icon"},[n("uc-icon",{class:"c-icon--"+e.type,attrs:{glyph:e.type}})],1):t._e(),t._v(" "),n("img",{staticClass:"lazyload",attrs:{"data-src":e.image,alt:e.title}})])]),t._v(" "),n("a",{staticClass:"c-teaser__link",attrs:{href:e.url}},[t._v(t._s(e.title))]),t._v(" "),e.subheading?n("div",{staticClass:"c-teaser__subtitle"},[t._v(t._s(e.subheading))]):t._e()])}),0)])],1)},[],!1,null,null,null);e.default=o.exports}}]);