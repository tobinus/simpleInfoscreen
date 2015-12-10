/* fading.transitions.SlideShow.js - Smooth and simple fade transitions */

var cutTransition = {

    durationS2S: 0,
    durationS2B: 1,
    durationB2S: 1,

// Transition from current to next
startTransitionS2S: function (current, next, onCompleteFunction)
{
    next.css( { opacity: 1, display: 'block' } );
    onCompleteFunction();
},

// Transition from slide to background (black)
startTransitionS2B: function (current, ui, onCompleteFunction)
{
    current.css( { opacity: 0.5 } );
    ui.css( { opacity: 0.5 } );
    setTimeout( function ()
    {
        current.css( { opacity: 0, display: 'none' } );
        ui.hide();
        onCompleteFunction()
    }, 1000 );
},

// Transition from background (black) to slide
startTransitionB2S: function (next, ui, onCompleteFunction)
{
    next.css( {
        opacity: 0.5,
        display: 'block'
    } );
    ui.css( {
        opacity: 0.5,
        display: 'block'
    } );
    setTimeout( function ()
    {
        next.css( 'opacity', 1 );
        ui.css( { opacity: 1 } );
        onCompleteFunction()
    }, 1000 );
}
};