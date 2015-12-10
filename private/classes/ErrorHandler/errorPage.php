<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vi er straks tilbake</title>
    <script>setTimeout(function () {location.reload(true);}, 1*60*1000);</script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="<?=$url?>/bootstrap/css/bootstrap.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="<?=$url?>/bootstrap/css/bootstrap-theme.css">


    <script>
        function Quote(content, author, source)
        {
            var sourceHtml;
         /*   if (!(typeof source === 'undefined')) {
                sourceHtml = " i <cite title='"+source+"'>"+source+"</cite>";
            } else { */
                sourceHtml = '';
        //   }
            this.html = "<blockquote style=\"margin-top: 1%\"><p>"+content+
                    "</p><footer>"+author+sourceHtml + "</footer></blockquote>";
        }

        var quotes = [
                new Quote( "“Sometimes a clearly defined error is the only way to discover the truth” ",
                "Benjamin Wiker", "The Mystery of the Periodic Table"),
                new Quote( " “To err is human, to persist in error is diabolical.” ",
                "Georges Canguilhem", "Ideology and Rationality in the History of the Life Sciences"),
                new Quote( " “Mistakes are a part of being human. Precious life lessons that can only be learned the hard way. Unless it's a fatal mistake, which, at least, others can learn from.” ",
                "Al Franken"),
                new Quote(" “To err is human, to purr is feline.” ",
                "Robert Byrne"),
               /* new Quote(" “When I was a kid, they had a saying, 'to err is human but to really fuck it up takes a computer.’ ” ",
                "Benjamin R. Smith", "Atlas"),*/
        ];

        var quote = quotes[Math.floor(Math.random() * quotes.length)].html;
    </script>

    <style>
        body {
            background: linear-gradient(to bottom, #5bc0de 0%, white 100%) no-repeat fixed;
            background: -webkit-linear-gradient(top, #5bc0de 0%, white 100%) no-repeat fixed;
        }
        #rotatedRight {
            -webkit-transform: rotate(5deg); /* Chrome, Safari, Opera */
            transform: rotate(5deg);
        }
        #rotatedLeft {
            -webkit-transform: rotate(-1deg); /* Chrome, Safari, Opera */
            transform: rotate(-1deg);
        }
    </style>
</head>
<body style="">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 text-center" id="rotatedRight">
            <h1>Søren klype
            <small>nå oppstod det en teknisk feil</small></h1>
        </div>
        </div>
    <div class="row">
        <div class="col-md-9 col-md-offset-1">
            <p id="rotatedLeft">Si gjerne i fra i kiosken, slik at riktig person kan kontaktes.
            Feilen blir jo ikke fikset hvis ingen vet om den :) Det er, etter alt å dømme, ingenting galt
            med denne skjermen, datamaskinen osv., siden feilen ligger hos <em>nettsida</em> som vi bruker.</p>
            <!-- <p>Se informasjon i nærheten av denne skjermen for
            å finne ut hvem du bør varsle.</p> -->

            <script>
                document.write(quote);
            </script>
        </div>
    </div>
</div>
</body>
</html>