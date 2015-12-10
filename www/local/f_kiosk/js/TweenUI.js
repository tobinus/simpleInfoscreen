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
timeline = new TimelineLite({paused:true, onComplete:startPlaying});
TweenLite.defaultOverwrite = "none";
timeline.insert(new TweenLite({}, 0.0, {}), 10.0);
var layer_1 = document.getElementById("tui-20");
timeline.insert(TweenLite.to(layer_1, 4.63, {css: {y: 409, x: 412}, ease: Power1.easeOut}), 4.09);
timeline.insert(TweenLite.to(layer_1, 1.38, {css: {scale: 1.79}, ease: Power1.easeOut}), 8.62);
timeline.insert(TweenLite.to(layer_1, 0.5, {css: {autoAlpha: 1.0}, ease: Power1.easeOut}), 3.467935871743487);
TweenLite.set(layer_1, {css: {autoAlpha: 0.0, x: 437, y: 636, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_2 = document.getElementById("tui-19");
timeline.insert(TweenLite.to(layer_2, 1.8, {css: {autoAlpha: 1.0}, ease: Power1.easeOut}), 2.79);
TweenLite.set(layer_2, {css: {autoAlpha: 0.0, x: 902, y: 159, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_3 = document.getElementById("tui-17");
timeline.insert(TweenLite.to(layer_3, 1.36, {css: {y: 86, x: 882}, ease: Power1.easeOut}), 1.88);
timeline.insert(TweenLite.to(layer_3, 0.501002004008016, {css: {autoAlpha: 1.0}, ease: Power1.easeOut}), 1.8637274549098195);
TweenLite.set(layer_3, {css: {autoAlpha: 0.0, x: 383, y: 628, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_4 = document.getElementById("tui-16");
timeline.insert(TweenLite.to(layer_4, 0.78, {css: {autoAlpha: 1.0}, ease: Power1.easeOut}), 1.62);
TweenLite.set(layer_4, {css: {autoAlpha: 0.0, x: 512, y: 108, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_5 = document.getElementById("tui-15");
timeline.insert(TweenLite.to(layer_5, 2.16, {css: {y: 160, x: 265}, ease: Power1.easeOut}), 0.0);
TweenLite.set(layer_5, {css: {autoAlpha: 1.0, x: -260, y: 425, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_6 = document.getElementById("tui-14");
TweenLite.set(layer_6, {css: {autoAlpha: 1.0, x: 0, y: 0, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
tui_go();
}
WebFontConfig = {google: { families: ['Lora'] }, active: function() { tui_go(); },inactive: function() { tui_go(); } };(function() {var wf = document.createElement('script');wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +'://ajax.googleapis.com/ajax/libs/webfont/1.0.31/webfont.js';wf.type = 'text/javascript';wf.async = 'true';var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(wf, s);})();
var imgcnt = 3;
tweenui.imgcntd = function(e) { var id = e.id.replace(/[^0-9.]/g, "");e.parentNode.removeChild(e);document.getElementById('tui-' + id).appendChild(e); --imgcnt; if (imgcnt==0) { tui_go(); }}
}(window.tweenui = window.tweenui || {}));