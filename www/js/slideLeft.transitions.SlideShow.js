/* fading.transitions.SlideShow.js - Smooth and simple fade transitions */
var slideLeftTransition = {
    durationS2S : 1,
    durationS2B : 1,
    durationB2S : 1,
    useScrolling : true,
    power : Power3,

    // Transition from current to next
    startTransitionS2S: function(current, next, onCompleteFunction)
    {
        next.css( { left: '100%', display: 'block' } );

        if (this.useScrolling)
        {
            TweenLite.to(
                window,
                this.durationS2S,
                {
                    scrollTo: { x: 'max', y: 0, autokill: false },
                    ease: this.power.easeInOut,
                    onComplete: function ()
                    {
                        current.css( { display: 'none' } );
                        next.css( { left: 0 } );
                        window.scrollTo( 0, 0 );
                        onCompleteFunction();
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
                    left: '-=100%',
                    ease: this.power.easeInOut,
                    onComplete: onCompleteFunction,
                }
            )
        }
    },

    // Transition from slide to background (black)
    startTransitionS2B: function(current, ui, onCompleteFunction)
    {
        if (this.useScrolling)
        {
            current.css( { paddingRight: '100%' } );
            TweenLite.to(
                window,
                this.durationS2B,
                {
                    scrollTo: { x: 'max', y: 0, autokill: false },
                    ease: this.power.easeInOut,
                    onComplete: function ()
                    {
                        onCompleteFunction();
                        current.css( { paddingRight: 0, display: 'none' } );
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
                { left: 0 },
                {
                    left: '-100%',
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
            next.css( { paddingLeft: '100%', display: 'block' } );
            TweenLite.to(
                window,
                this.durationB2S,
                {
                    scrollTo: { x: 'max', y: 0, autokill: false },
                    ease: this.power.easeOut,
                    onComplete: function ()
                    {
                        var body = $( "body" );
                        body.css( { width: '200%' } );
                        next.css( { paddingLeft: 0 } );
                        body.css( { width: '100%' } );
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
                    left: '100%',
                    display: "block"
                },
                {
                    left: 0,
                    ease: this.power.easeInOut,
                    onComplete: onCompleteFunction
                }
            );
        }
        fadeInUi( ui );
    },
};