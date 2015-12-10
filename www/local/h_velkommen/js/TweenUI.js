(function(tweenui) {
var timeline;
function startPlaying() { timeline.play(0); }
function g(name) { name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]"); var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"); var results = regex.exec(window.location.search); if(results == null) return ""; else return decodeURIComponent(results[1].replace(/\+/g, " ")); }
var res = function() { var bd = document.getElementsByTagName('body')[0];bd.style.webkitTransform = 'scale(' + window.innerWidth/1920 + ')';bd.style.transform = 'scale(' + window.innerWidth/1920 + ')';}
if (g('s')==1) { window.onresize = res; }
var loadcnt = 3;
function tui_go() {
--loadcnt; if (loadcnt==0) {
var l = g("l"); var ctl = document.getElementById("tui-ctl");
if (l!="" && ctl) { if (g('a')==1) { l = l + encodeURIComponent(ctl.href) } ctl.href=l; }
if (typeof(tweenui.exp) == "function") { tweenui.exp(); }
TweenLite.set(document.getElementById("tui-ct"), {css: {alpha: 0}});
if (g('s')==1) { res(); }
startPlaying();
}
}
tweenui.init = function() {
timeline = new TimelineLite({paused:true});
TweenLite.defaultOverwrite = "none";
timeline.insert(new TweenLite({}, 0.0, {}), 10.0);
var layer_1 = document.getElementById("tui-11");
timeline.insert(TweenLite.to(layer_1, 0.5, {css: {autoAlpha: 1.0}, ease: Power1.easeOut}), 7.335671342685371);
timeline.insert(TweenLite.to(layer_1, 2.064128256513026, {css: {rotationX: 0.0, rotationY: 0.0,rotationZ: 0.0}, ease: Power1.easeOut}), 7.935871743486974);
TweenLite.set(layer_1, {css: {autoAlpha: 0.0, x: 1216, y: 259, scale: 3.632183908045977, rotationX: 0.0, rotationY: 180.0, rotationZ: 0.0}});
var layer_2 = document.getElementById("tui-10");
timeline.insert(TweenLite.to(layer_2, 0.501002004008016, {css: {autoAlpha: 1.0}, ease: Power1.easeOut}), 5.2304609218436875);
timeline.insert(TweenLite.to(layer_2, 2.0440881763527052, {css: {y: 353, x: 203}, ease: Power1.easeOut}), 5.751503006012024);
TweenLite.set(layer_2, {css: {autoAlpha: 0.0, x: 1030, y: 525, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_3 = document.getElementById("tui-9");
timeline.insert(TweenLite.to(layer_3, 0.501002004008016, {css: {autoAlpha: 1.0}, ease: Power1.easeOut}), 3.106212424849699);
timeline.insert(TweenLite.to(layer_3, 1.6833667334669338, {css: {y: 184, x: 711}, ease: Power1.easeOut}), 3.5871743486973946);
TweenLite.set(layer_3, {css: {autoAlpha: 0.0, x: 1514, y: 538, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_4 = document.getElementById("tui-8");
timeline.insert(TweenLite.to(layer_4, 0.8817635270541082, {css: {autoAlpha: 1.0}, ease: Power1.easeOut}), 0.0);
timeline.insert(TweenLite.to(layer_4, 2.3847695390781563, {css: {y: 6, x: 14}, ease: Power1.easeOut}), 0.501002004008016);
TweenLite.set(layer_4, {css: {autoAlpha: 0.0, x: 39, y: 530, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
tui_go();
}
WebFontConfig = {google: { families: ['Lora'] }, active: function() { tui_go(); },inactive: function() { tui_go(); } };(function() {var wf = document.createElement('script');wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +'://ajax.googleapis.com/ajax/libs/webfont/1.0.31/webfont.js';wf.type = 'text/javascript';wf.async = 'true';var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(wf, s);})();
var imgcnt = 1;
tweenui.imgcntd = function(e) { var id = e.id.replace(/[^0-9.]/g, "");e.parentNode.removeChild(e);document.getElementById('tui-' + id).appendChild(e); --imgcnt; if (imgcnt==0) { tui_go(); }}
}(window.tweenui = window.tweenui || {}));