(window.webpackJsonp=window.webpackJsonp||[]).push([[22],{"48Wg":function(t,n,o){"use strict";o.r(n);var e={name:"UcMediaButton",components:{UcIcon:function(){return Promise.resolve().then(o.bind(null,"qeU3"))}},props:{callback:{type:Function,required:!0},duration:String,buttontype:{type:String,default:"video"},inline:{type:Boolean,default:!1},url:String,tag:{type:String,default:"div"}},data:function(){return{buttonIcon:"play",attrs:{},showPodcastLogo:!1}},computed:{classes:function(){var t="c-media-button--".concat(this.buttontype);return this.inline&&(t+=" c-media-button--inline"),t}},mounted:function(){"podcast"===this.buttontype&&(this.buttonIcon="podcast",this.displayPodcastLogo()),"a"===this.tag&&(this.attrs.href=this.url)},methods:{displayPodcastLogo:function(){if(!this.inline){var t=this.$refs.podcastLogo;this.$el.parentElement.appendChild(t.$el),this.showPodcastLogo=!0}}}},a=o("KHd+"),s=Object(a.a)(e,function(){var t=this,n=t.$createElement,o=t._self._c||n;return o("div",{staticClass:"c-media-button",class:t.classes},[o("div",{staticClass:"c-media-button__ctn"},[o(t.tag,t._b({tag:"component",on:{click:function(n){return n.preventDefault(),t.callback(n)}}},"component",t.attrs,!1),[o("uc-icon",{attrs:{glyph:t.buttonIcon,"class-name":""}}),t._v(" "),t.duration&&"podcast"!==t.buttontype?o("div",{staticClass:"c-media-button__duration"},[t._v("\n        "+t._s(t.duration)+"\n      ")]):t._e()],1)],1),t._v(" "),o("uc-icon",{directives:[{name:"show",rawName:"v-show",value:t.showPodcastLogo,expression:"showPodcastLogo"}],ref:"podcastLogo",attrs:{glyph:"podcast-uchicago"}})],1)},[],!1,null,null,null);n.default=s.exports}}]);