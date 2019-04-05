$(function(){
    function getMonday() {
        date = new Date();
        var day = date.getDay(),
        diff = date.getDate() - day + (day == 0 ? -6:1);
        return new Date(date.setDate(diff));
    }

    $( "#from-date" ).datepicker({
        maxDate: 0
    }).datepicker("setDate", getMonday());

    function heatmap() {
            Array.max = function( array ){
                return Math.max.apply( Math, array );
            };

            // get all values
            var counts= $('.score').map(function() {
                return parseInt($(this).text());
            }).get();

            var max = Array.max(counts);

            xr = 255;
            xg = 255;
            xb = 255;

            yr = 40;
            yg = 150;
            yb = 200;

            n = 100;

            $('.score').each(function(){
                var val = parseInt($(this).text());
                var pos = parseInt((Math.round((val/max)*100)).toFixed(0));
                red = parseInt((xr + (( pos * (yr - xr)) / (n-1))).toFixed(0));
                green = parseInt((xg + (( pos * (yg - xg)) / (n-1))).toFixed(0));
                blue = parseInt((xb + (( pos * (yb - xb)) / (n-1))).toFixed(0));
                clr = 'rgb('+red+','+green+','+blue+')';
                $(this).parent().css({backgroundColor:clr});
            });
    }

    function mugDetect(){
        $('.mugs').filter(function(){
            var mugs = $(this).text(), total = $(this).siblings('.total').text();
            if (mugs / total * 100 > 10) $(this).css('font-weight', 'bold');
        });
    }

    function userEdit(){
        var id, name;
        $('.edit').click(function(){
            $('#user-edit').show(500);
            var id = $(this).attr('href').substr(1), name = $(this).text(), notes = $('#notes-'+id).text();
            $('#edit-id').val(id);
            $('#edit-name').val(name);
            $('#notes').val(notes);
            return false;
        });
        $(document).click(function() {
            $('#user-edit').hide(500);
        });
        $('#user-edit').click(function(event){
            event.stopPropagation();
        });
    }

    var request;
    $('#make-report').submit(function(event){
        if (request) request.abort();
        var $form = $(this), $inputs = $form.find("button, text"), $data = $form.serialize();
        $inputs.prop("disabled", true);
        $('#report-content').html('<img id="loader" src="images/ajax-loader.gif"></img>');
        request = $.post('get-logs.php', $data, function(response) {
            $('#report-content').html(response);
            userEdit();
        });
        request.always(function () {
            $inputs.prop("disabled", false);
            $('#loader').hide();
        });
        request.done(function (response){
            heatmap();
            mugDetect();
        });
        event.preventDefault();
    });
    
    function shipOff(event) {
        if (request) request.abort();
        var result = event.target.result;
        var fileName = document.getElementById('file').files[0].name; //Should be 'picture.jpg'
        request = $.post('report-upload.php', {name: fileName, data: encodeURIComponent(result)});
        
        request.done(function (response){
            var data = $.parseJSON(response);
            if (data.error === '') {
                window.location = "/report/?name="+fileName.slice(0,-4);
            } else {
                $('#ul-err').html(data.error);
            }
        });
        
    }
    
    $('#upload').submit(function(event) {
        var file = document.getElementById('file').files[0]; //Files[0] = 1st file
        if (file.type === "text/xml") {
            var reader = new FileReader();
            reader.readAsText(file, 'UTF-8');
            reader.onload = shipOff;
            //reader.onloadstart = ...
            //reader.onprogress = ... <-- Allows you to update a progress bar.
            //reader.onabort = ...
            //reader.onerror = ...
            //reader.onloadend = ...
        } else {
            $('#ul-err').html("Invalid file type");
        }

        event.preventDefault();
    });

    $('#user-edit').submit(function(event){
        if (request) request.abort();
        var $form = $(this), $inputs = $form.find("button, textarea"), $data = $form.serialize();
        $inputs.prop("disabled", true);
        request = $.post('add-notes.php', $data);
        request.always(function () {
            $inputs.prop("disabled", false);
        });
        request.done(function (response){
            $('#user-edit').hide(500);
            var id = $('#edit-id').val(), notes = $('#notes').val();
            $('#notes-'+id).html(notes);
        });
        event.preventDefault();
    });
        $('#add-stats').submit(function(event){
            event.preventDefault();
        if (request) request.abort();
        var $form = $(this), $inputs = $form.find("button, textarea");
        var msg, data = $form.find("textarea").val().split(/\n{2,}/g);
        var p, mtch, obj = '[', name, id, spd, str, def, dex, tot, failed=0;
        
        function parseStats(p, s) {
            var x = s.match(p);
            if (x === null) return 0;
            else return parseInt(x[1].replace(/,/g, ''));
        };
        
        for (p in data)
        {
            name = data[p].match(/name:\s*([\w-]+)[\W]/i);
            id = parseStats(/name:.+[^A-Za-z0-9]+([0-9,]+)/i, data[p]);
            if (name === null || id === 0) failed++;
            else {
                var stats = new Array(
                    parseStats(/sp.*:\s*([0-9,]+)/i, data[p]),
                    parseStats(/str.*:\s*([0-9,]+)/i, data[p]),
                    parseStats(/def.*:\s*([0-9,]+)/i, data[p]),
                    parseStats(/dex.*:\s*([0-9,]+)/i, data[p]),
                    parseStats(/tot.*:\s*([0-9,]+)/i, data[p])
                ), c=0, miss;
                for (p in stats) {
                    if (stats[p] === 0) {
                        miss=p;
                        c++;
                    }
                }
                if (c===1) {
                    var sum = stats[0]+stats[1]+stats[2]+stats[3];
                    if (p===4) stats[miss] = sum;
                    else stats[miss] = stats[4] - sum;
                }
                obj += '["'+name[1]+'",'+id+','+stats[0]+','+stats[1]+','+stats[2]+','+stats[3]+','+stats[4]+'],';
            }
        };
        if (obj === '[') $('#status-rslt').html("Update failed");
        else{
            data = obj.replace(/,$/, "]");
            $inputs.prop("disabled", true);
            request = $.post('stats/add-stats.php', {'player': encodeURI(data)});
            request.always(function () {
                $inputs.prop("disabled", false);
                $('#status-rslt').html("Update failed");
            });
            request.done(function (response){
                data = $.parseJSON(response), msg = 'Updated: ';
                for (p in data[0]){
                    msg += '<a href="http://gregos.it.cx/stats/?id='+data[0][p][0]+'" target="_blank">'+data[0][p][1]+"</a>, ";
                };
                msg = msg.replace(/,\s$/, "");
                if (failed > 0) msg += '<br />Failed: '+failed;
                $('#status-rslt').html(msg);
                $form.find("textarea").val('');
            });
        }
    });

    $(".tabs").click(function(){
        if ($(this).attr('class') !== 'tabs current'){
            var $hide = $(".show");
            var $show = $($(this).attr("id").replace(/tab-/,"#view-"));

            $(".tabs").attr('class', 'tabs');
            $(this).attr('class', 'tabs current');
            $hide.attr('class', 'hide');
            $hide.hide();
            $show.fadeIn(300).css('display','inline-block');
            $show.attr('class', 'show');
        };
    });
});