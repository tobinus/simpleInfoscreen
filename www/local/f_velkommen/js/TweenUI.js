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
var layer_1 = document.getElementById("tui-13");
timeline.insert(TweenLite.to(layer_1, 5.65, {css: {y: 101, x: 129}, ease: Power1.easeOut}), 3.55);
TweenLite.set(layer_1, {css: {autoAlpha: 1.0, x: 1294, y: -55, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_2 = document.getElementById("tui-12");
timeline.insert(TweenLite.to(layer_2, 3.91, {css: {y: 42, x: 130}, ease: Power1.easeOut}), 0.0);
TweenLite.set(layer_2, {css: {autoAlpha: 1.0, x: 1284, y: 755, scale: 1.0, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
var layer_3 = document.getElementById("tui-10");
TweenLite.set(layer_3, {css: {autoAlpha: 1.0, x: 403, y: 228, scale: 1.79, rotationX: 0.0, rotationY: 0.0, rotationZ: 0.0}});
tui_go();
}
WebFontConfig = {google: { families: ['Lora'] }, active: function() { tui_go(); },inactive: function() { tui_go(); } };(function() {var wf = document.createElement('script');wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +'://ajax.googleapis.com/ajax/libs/webfont/1.0.31/webfont.js';wf.type = 'text/javascript';wf.async = 'true';var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(wf, s);})();
var imgcnt = 1;
tweenui.imgcntd = function(e) { var id = e.id.replace(/[^0-9.]/g, "");e.parentNode.removeChild(e);document.getElementById('tui-' + id).appendChild(e); --imgcnt; if (imgcnt==0) { tui_go(); }}
}(window.tweenui = window.tweenui || {}));