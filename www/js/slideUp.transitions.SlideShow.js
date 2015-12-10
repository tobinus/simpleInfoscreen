/* fading.transitions.SlideShow.js - Smooth and simple fade transitions */

var slideUpTransition = {

    durationS2S : 1,
    durationS2B : 1,
    durationB2S : 1,
    useScrolling : true,
    power: Power3,

    // Transition from current to next
    startTransitionS2S: function(current, next, onCompleteFunction)
    {
        next.css( { top: '100%', display: 'block' } );
        if (this.useScrolling)
        {
            TweenLite.to(
                window,
                this.durationS2S,
                {
                    scrollTo: { x: 0, y: 'max' },
                    ease: this.power.easeInOut,
                    onComplete: function ()
                    {
                        onCompleteFunction();
                        next.css( { top: 0 } );
                        window.scrollTo( 0, 0 );
                    }


                }
            );
        } else
        {
            TweenLite.to
            (
                [current, next],
                this.durationS2S,
                {
                    top: '-=100%',
                    ease: this.power.easeInOut,
                    onComplete: onCompleteFunction
                }
            )
        }
    },

    // Transition from slide to background (black)
    startTransitionS2B: function(current, ui, onCompleteFunction)
    {
        if (this.useScrolling)
        {
            TweenLite.to(
                window,
                this.durationS2B,
                {
                    scrollTo: { x: 0, y: 'max', autokill: false },
                    ease: this.power.easeInOut,
                    onComplete: function ()
                    {
                        onCompleteFunction();
                        current.css( { display: 'none' } );
                        window.scrollTo( 0, 0 );
                    }
                }
            );
        } else
        {
            TweenLite.fromTo
            (
                current,
                this.durationS2B,
                { top: 0 },
                {
                    top: '-100%',
                    ease: this.power.easeInOut,
                    onComplete: onCompleteFunction
                }
            );
        }
        fadeOutUi( ui );
    },

    // Transition from background (black) to slide
    startTransitionB2S: function(next, ui, onCompleteFunction)
    {
        if (this.useScrolling)
        {
            next.css( { top: '100%', display: 'block' } );
            TweenLite.to(
                window,
                this.durationB2S,
                {
                    scrollTo: { x: 0, y: 'max', autokill: false },
                    ease: this.power.easeOut,
                    onComplete: function ()
                    {
                        var body = $( "body" );
                        body.css( { height: '200%' } );
                        next.css( { top: 0 } );
                        body.css( { height: '100%' } );
                        window.scrollTo( 0, 0 );
                        onCompleteFunction();
                    }
                }
            );
        } else
        {
            TweenLite.fromTo
            (
                next,
                this.durationB2S,
                {
                    top: '100%',
                    display: "block"
                },
                {
                    top: 0,
                    ease: this.power.easeInOut,
                    onComplete: onCompleteFunction
                }
            );
        }
        fadeInUi( ui );
    }
}
$( document ).ready(function() {
    // $dummy might not be defined yet
    $( "#dummy" ).css({bottom: '-100%'});
})