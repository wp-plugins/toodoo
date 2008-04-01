document.observe('dom:loaded', function(){
    $$('a[class="snapshot"]').each(function(element){
        new Ajax.Request(toodoopath + 'toodoo-wrap.php', {
          asynchronous:false,
          method:'get',
          parameters: {url: element},
          requestHeaders: {Accept: 'application/json'},
          onSuccess: function(transport){
            var json = transport.responseText.evalJSON(true);

            var html = '';
            var rate = '';
            
            for (var i=0; i<5; i++) {
                if (parseInt(json.rating) < 2*i) {
                    rate = rate + '<img src="' + toodoopath + 'images/rate/star0.png">';
                }
                else {
                    rate = rate + '<img src="' + toodoopath + 'images/rate/star1.png">';
                }
            }

            if (json.favicon_url) favicon = '<img src="' + json.favicon_url + '" height="16"/>'; else favicon = '';

            html = html + ' <div id="snap">';
            html = html + '     <div id="rate">' + rate + '</div>';
            html = html + '     <div id="block">';
            html = html + '         <div id="thumbnail"><a href="http://toodoo.ru/blog/'+ json.id +'/index"><img src="' + json.thumbnail_url +'" border="0"/></a></div>';
            html = html + '         <div id="url">'+ favicon +' <br /><a href="' +element+ '">' +element+ '</a></div>';
            html = html + '     </div>';
            html = html + '</div>';

            wnd = new Tip(
                element,
                html, {
                    hideOn: {
                        element: 'tip',
                        event: 'mouseout'
                    },
                    fixed: true,
                    hideAfter: 0.5,
                    effect: 'appear',
                    hook: {
                        tip: 'topLeft',
                        target: 'topLeft'
                    },
                    offset: {
                        x: 0,
                        y: 15
                    }
                }
            );
          }
        });
    });
});
