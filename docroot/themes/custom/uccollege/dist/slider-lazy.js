(window.webpackJsonp=window.webpackJsonp||[]).push([[30],{ueJB:function(e,i,s){"use strict";s.r(i);var t={name:"UcSlider",props:{breakpoint:{type:Number,default:768}},data:function(){return{realBreakpoint:this.breakpoint,shouldSwipe:!1,swiperObject:null,swiperWrapper:null,swiperSlides:null,swiperOptions:{init:!1,loop:!1,slidesPerView:1,slidesPerGroup:1,direction:"horizontal",spaceBetween:10}}},mounted:function(){var e=this;0<this.$el.getElementsByClassName("c-callout__text").length&&(e.realBreakpoint=1024),4<e.$el.getElementsByTagName("li").length&&(e.realBreakpoint=0),e.checkBreakpoint(),window.addEventListener("resize",this.debounce(200,function(){e.checkBreakpoint()}))},updated:function(){this.checkBreakpoint()},methods:{checkBreakpoint:function(){this.shouldSwipe=window.innerWidth<=this.realBreakpoint||0===this.realBreakpoint,this.shouldSwipe?this.prepSlider():this.swiperObject&&this.revertSlider()},prepSlider:function(){var e=this;e.swiperWrapper=e.$el.firstElementChild,e.swiperWrapper.classList.add("swiper-wrapper"),e.swiperSlides=e.swiperWrapper.children,[].slice.call(e.swiperSlides).forEach(function(e){e.classList.add("swiper-slide")}),e.initSlider()},setSlideCounts:function(){1024<=window.innerWidth?(this.swiperOptions.slidesPerView=4,this.swiperOptions.slidesPerGroup=4,this.swiperOptions.spaceBetween=80,this.swiperOptions.navigation={nextEl:".swiper-button-next",prevEl:".swiper-button-prev"}):768<=window.innerWidth?(this.swiperOptions.slidesPerView=2,this.swiperOptions.slidesPerGroup=2):(this.swiperOptions.slidesPerView=1,this.swiperOptions.slidesPerGroup=1)},initSlider:function(){var e=this;e.setSlideCounts(),s.e(38).then(s.bind(null,"QqDX")).then(function(i){e.swiperObject=new i.Swiper(e.$el,e.swiperOptions),e.$nextTick(function(){e.swiperObject.init()})})},revertSlider:function(){this.swiperWrapper.classList.remove("swiper-wrapper");for(var e=0;e<this.swiperSlides.length;e+=1)this.swiperSlides[e].classList.remove("swiper-slide");this.swiperObject.destroy()},nextSlide:function(){this.swiperObject.slideNext()},prevSlide:function(){this.swiperObject.slidePrev()}}},r=s("KHd+"),n=Object(r.a)(t,function(){var e=this,i=e.$createElement;return(e._self._c||i)("div",{class:{"c-slider swiper-container":e.shouldSwipe}},[e._t("default",null,{nextSlide:e.nextSlide,prevSlide:e.prevSlide})],2)},[],!1,null,null,null);i.default=n.exports}}]);