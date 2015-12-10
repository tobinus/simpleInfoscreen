/* fading.transitions.SlideShow.js - Smooth and simple fade transitions */

var fadeTransition = {
    durationS2S : 2,
    durationS2B : 1,
    durationB2S : 1,

    // Transition from current to next
    startTransitionS2S: function(current, next, onCompleteFunction)
    {
        current.css({zIndex:2});
        next.css({zIndex:1, display: 'block', opacity: 1});
        TweenLite.fromTo
        (
            current, // element to animate
            1, // duration in seconds
            { // start properties
                opacity: 1,
            },
            { // end properties
                opacity: 0,
                ease: Power2.easeInOut,
                onComplete: onCompleteFunction // callback function
            }
        );
    },

    // Transition from slide to background (black)
    startTransitionS2B: function(current, ui, onCompleteFunction)
    {
        TweenLite.fromTo
        (
            [current, ui],
            1,
            { opacity: 1 },
            {
                opacity: 0,
                ease: Power2.easeInOut,
                onComplete: onCompleteFunction
            }
        );
    },

    // Transition from background (black) to slide
    startTransitionB2S: function(next, ui, onCompleteFunction)
    {
        TweenLite.fromTo
        (
            [next, ui],
            1,
            {
                opacity: 0,
                display: "block"
            },
            {
                opacity: 1,
                ease: Power2.easeInOut,
                onComplete: onCompleteFunction
            }
        );

    },
};