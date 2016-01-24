/** Slide(url, duration): create a new Slide object
 * url: url to load
 * duration: amount of seconds to stay on this slide,
 *           counted from the start of the transition
 * loadTime: amount of seconds to allocate to loading */
/**
 * Represents a single slide.
 * @param {String} url The url which will be loaded and shown, relative to this folder
 * @param {int} duration Amount of seconds that slide will be shown.
 * The time starts ticking once the transition animation _starts_.
 * @param {int} [loadTime=1] Amount of seconds this slide will be given to load.
 * @constructor
 */
function Slide(url, duration, loadTime)
{
    if (typeof loadTime === 'undefined') { loadTime = 1; }

    this.url = url;
    this.duration = duration;
    this.loadTime = loadTime;
}

// SlideShow(Slide slide1, Slide slide2, ...)
// An object representing the entirity of the slideshow
function SlideShow()
{
    // slides: array consisting of the slides in order
    this.slides = [];
    for (var i = 0; i < arguments.length; i++)
    {
        this.slides[i] = arguments[i];
    }
    
    // currentSlide: index of the most recently loaded slide
    this.currentSlide = 0;
    
    // numSlides: number of slides in the slideshow
    this.numSlides = arguments.length;

    /**
     * Increments the counter, and returns the new slide.
     */
    this.next = function () 
    {
        this.currentSlide = this._nextIndex();

        return this.current();
    };

    /**
     * Returns the next slide, but does not touch the counter
     * @returns {Slide}
     */
    this.peek = function ()
    {
        return this.slides[this._nextIndex()];
    };

    /**
     * Returns the next, valid index for slides.
     * @returns {number}
     * @private
     */
    this._nextIndex = function ()
    {
        var newIndex = this.currentSlide + 1;
        if (newIndex == this.numSlides)
        {
            newIndex = 0;
        }

        return newIndex;
    };
    
    // current(): return the current slide (progress with next())
    this.current = function () 
    {
        return this.slides[this.currentSlide];
    };
}

var mustReload = false;
var loadTimeoutRef;
var nextTransition = 0;
var slideDone = false;
var loadingDone = false;
function nextSlide() {
    if (!mustReload) {
        $( "#next" ).one( "load" , nextSlideOnLoad)
        // Actually start loading the next slide
                    .attr( 'src' , slideShow.next().url);
        
    }
}

function nextSlideOnLoad()
{
    clearTimeout(loadTimeoutRef);
    // Mark the page as loaded, so the progress bar will init transition when done
    loadingDone = true;
    // Did we finish loading after the time?
    if (
        //1 !blackTransition &&
        (firstTransition || slideDone)) {
        // We're overdue - move forward now!
        // (Using setTimeout to relieve the browser from some stress)
        setTimeout(nextSlideTransition, 0);
    }
}

function progressBarDone()
{
    // Mark the slide as done displaying
    slideDone = true;
    // Must we load another page?
    if (mustReload)
    {
        transition.startTransitionS2B( $( "#current" ), $ui, function ()
        {location.reload( true );
        } );
    } else
    // Is the next page ready to go?
    if (loadingDone) {
        // GO GO GO
        setTimeout(nextSlideTransition, 0);
    } else {
        // We're starting to go overdue - cancel the loading if we pass the threshold
        loadTimeout('next');
    /* //1  Commented out because it's often more distracting than useful to go to background while overdue
        if (ENABLE_SLIDE_PROGRESS_BAR) {
            fromBlack = true;
            blackTransition = true;
            transition.startTransitionS2B( $( "#current" ), $ui, function () {
                if (loadingDone) {
                    nextSlideTransition();
                    fromBlack = false;
                }
                blackTransition = false;
            });
        } */
    }
}

function timeToNextTransition()
{
    return (new Date()).getTime() + (slideShow.current().duration * 1000);
}

var firstTransition = true;
/* //1 var fromBlack = false;
var blackTransition = false;*/
function nextSlideTransition()
{
    // This block is executed only after the next slide is done loading
    // Make sure the correct iframe is on top
    // #next is hidden, so it won't appear yet
    var current = $( "#current" ).css( "z-index" , 1);
    var next = $( "#next" ).css( {"z-index": 2});
    if (firstTransition
    //    || fromBlack
    )
    {
        firstTransition = false;
    /*  //1  blackTransition = false;
        fromBlack = false; */
        transition.startTransitionB2S(next, $ui, cleanupAfterTransition);
    } else {
        // Start fading in the next slide
        transition.startTransitionS2S(current, next, cleanupAfterTransition);
    }
}

function loadTimeout(id)
{
    loadTimeoutRef = setTimeout(function()
    {
        console.warn(slideShow.current().url + " hasn't loaded, even after "+SLIDE_LOAD_TIMEOUT+" seconds. Moving on.");
        // Try next URL. The "on load"-trigger will happen for this one instead.
        var frame = $("#" + id);
        loadTimeout(id);
        frame.attr( 'src' , slideShow.next().url);
    }, SLIDE_LOAD_TIMEOUT * 1000);
}

var $progressBar;
var $slideShowProgress;
function updateProgressElements()
{
    var duration = slideShow.current().duration;
    if (ENABLE_TOTAL_PROGRESS_TEXT) {
        // Update total progress
        $slideShowProgress.html("" + (slideShow.currentSlide + 1) + "/" + slideShow.numSlides);
    }
    if (ENABLE_SLIDE_PROGRESS_BAR) {
        // Update and animate local view progress
        TweenLite.fromTo( $progressBar, 1, { opacity: 0}, {opacity: 0.4, ease: Power1.easeInOut});
        TweenLite.fromTo( $progressBar, duration, { width: "8em" }, {
            width: 0,
            ease: Power0.easeNone,
            onComplete: progressBarDone,
        } );
    } else {
        setTimeout(progressBarDone, duration * 1000);
    }
}



// Set the slideshow in motion once the slideshow page itself is done loading
var intervalNewUpdateCheck; // will contain reference to the Interval that checks for updates
var $ui; // will contain the ui-elements
var $body; // try to guess
var $dummy; //empty element - use to force scrolling entire page
$( document ).ready(function ()
{
    // Tell the parent frame that we're alive every 58th second
    resetParentTimeout();
    setInterval(resetParentTimeout, 58000);
    // Save reference to progress bar and slide show progress
    $progressBar = $( "#slideProgress" );
    $slideShowProgress = $( "#slideShowProgress");
    // Save reference to all ui-elements
    $ui = $( ".ui" );
    // Save reference to the body
    $body = $( "body" );
    $dummy = $( "#dummy" );
    // Hide all iframes
    $( "iframe" ).hide();

    // Continue if the page doesn't load
    loadTimeoutRef = setTimeout(function(){loadTimeout("next");}, slideShow.current().loadTime * 1000);
    // Start the slideshow once the first slide is loaded
    $( "#next" ).one( "load" , nextSlideOnLoad)
    // Start loading the first slide
                   .attr('src', slideShow.current().url);
    /* Check periodically to see whether the slideshow has been changed on the server
       Set the update interval to the setting, ± 5 sec
       This is randomized so that the server won't get many requests on the very
       same time, even if all infoscreens started at the same moment (due to power
       outage, etc) */
    var updateInterval = UPDATE_CHECK_INTERVAL * 1000 + (10000*(Math.random()-0.5));
    intervalNewUpdateCheck = setInterval( checkIfNewUpdates, updateInterval );
    // Fade to black a little before the JavaScript-less refresh (from meta-tag)
    setTimeout(function () {
        transition.startTransitionS2B($("#current"),$ui,function () {})
    }, ((60 * 60) - 2) * 1000); // two seconds before one hour in milliseconds
});

function checkIfNewUpdates()
{
    $.ajax({

        // The URL for the request
        // (should be server-side page that checks if this page needs to be refreshed)
        url: "updateAvailable.php",

        cache: false,
        // The data to send (will be converted to a query string)
        data: {
            datetime: TIME_AT_LOAD
        },

        // Whether this is a POST or GET request
        type: "GET",

        // The type of data we expect back
        dataType : "text",

        mimeType: "text/plain",

        // Code to run if the request succeeds;
        // the response is passed to the function
        success: function( text ) {
            if (text == "true")
            {
                console.info("There are updates available. The page will be refreshed, and the slideshow will start from the beginning, during the next transition.");
                mustReload = true;
            } else if (text != "false")
            {
                console.warn("Response from server was neither 'true' nor 'false', was " + text);
            }
        },

        // Code to run if the request fails; the raw request and
        // status codes are passed to the function
        error: function( xhr, status, errorThrown ) {
            console.error("Check for updates failed: " + errorThrown + " (" + status + ")");
        }
    });
}

function cleanupAfterTransition()
{

    // The slide is done fading in when this is executed

    // Reset
    slideDone = false;
    loadingDone = false;
    // "Trade" id of current and next
    var wasCurrent = $( "#current" );
    var wasNext = $( "#next" );
    wasNext.attr( "id" , "current" );
    wasCurrent.attr( "id" , "next" );
    // Hide the slide that was displayed (now covered by the new slide, won't be visible)
    // This way it is ready to be faded in again
    wasCurrent.hide()
    // Unload it
                .attr( "src" , "");
    // Preload the next slide when appropiate
    setTimeout(nextSlide, Math.max(0, slideShow.current().duration - slideShow.peek().loadTime) * 1000);
    nextTransition = timeToNextTransition();
    updateProgressElements();
}

function fadeOutUi(ui)
{
    return TweenLite.to
    (
        ui,
        1,
        {opacity: 0,
            ease: Power2.easeInOut}
    );
}

function fadeInUi(ui)
{
    return TweenLite.fromTo
    (
        ui,
        1,
        { opacity: 0},
        {opacity: 1, ease: Power2.easeInOut}
    );
}

function resetParentTimeout()
{
    parent.resetReloadTimer();
}