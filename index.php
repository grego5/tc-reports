<!DOCTYPE html>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
        <style>
            body {background-color: #eaeaea}
            p, ul {margin: 0; padding:0; -webkit-margin-before: 0; -webkit-margin-after: 0; -webkit-margin-start: 0; -webkit-margin-end: 0px; -webkit-padding-start: 0;}
            a{-webkit-transition: all 0.2s ease; -moz-transition: all 0.2s ease; -o-transition: all 0.2s ease; transition: all 0.2s ease;}
            #site-body{width: 1080px; margin:0 auto; font-family: Optima, Segoe, "Segoe UI", Candara, Calibri, Arial, sans-serif; position: relative;}
            #report {margin:0 auto 200px auto; border-collapse: collapse; table-layout:fixed; width: 100%; box-shadow: -0px 0px 10px #5a5a5a;}
            #report thead, #report tfoot {background-color: #2d2d2d; color: #e4f1fb}
            #report th, #report td {padding: 2px 5px; width: 55px; overflow: hidden; text-overflow: ellipsis}
            #report th:first-child {width: 120px}
            #report th:last-child {width: 100%}
            .report-text {text-align: left}
            .report-text:first-child {font-weight: 600;}
            .report-num {text-align: center}
            th a {color: #e4f1fb}
            .edit, th a {text-decoration: none}
            .edit {color: #000}
            .edit:hover {color: #aed0ea}
            .tooltip {cursor: pointer}
        </style>
    </head>
    <body>
        <div id="site-body">
            <?php
            date_default_timezone_set('Europe/London');
            if(isset($_GET['name'])) $report = $_GET['name'];
            else
            {
                echo "<h2>Available reports:</h2>";
                foreach(glob('reports/*.xml') as $filename)
                {
                    $ex = explode(".", $filename, 2);
                    $report = substr($ex[0], 8);
                    echo "<a href='?name=$report'>$report</a> - ";
                    echo date("F d Y", filemtime($filename))."</br>";
                }
                die();
            };
            if(!file_exists("reports/$report.xml")) die ("<div>Report with name $report does not exist. For available reports go to <a href='http://gregos.it.cx/report'>back</a></div>");

            $xml = new DOMDocument();
            $xml->load("reports/$report.xml");

            $xsl = new DOMDocument;
            $xsl->load("report.xsl");

            $proc = new XSLTProcessor();
            $proc->importStyleSheet($xsl);

            echo $proc->transformToXML($xml);
        ?>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>$(function(){function heatmap(){Array.max=function(array){return Math.max.apply(Math,array);};var counts=$('.score').map(function(){return parseInt($(this).text());}).get();var max=Array.max(counts);xr=255;xg=255;xb=255;yr=40;yg=150;yb=200;n=100;$('.score').each(function(){var val=parseInt($(this).text());var pos=parseInt((Math.round((val/max)*100)).toFixed(0));red=parseInt((xr+((pos*(yr-xr))/(n-1))).toFixed(0));green=parseInt((xg+((pos*(yg-xg))/(n-1))).toFixed(0));blue=parseInt((xb+((pos*(yb-xb))/(n-1))).toFixed(0));clr='rgb('+red+','+green+','+blue+')';$(this).parent().css({backgroundColor:clr});});};function mugDetect(){$('.mugs').filter(function(){var mugs=$(this).text(),total=$(this).siblings('.total').text();if(mugs/total*100>10)$(this).css('font-weight','bold');});}heatmap();mugDetect();});function TSort_StoreDef(){this.sorting=[];this.nodes=[];this.rows=[];this.row_clones=[];this.sort_state=[];this.initialized=0;this.history=[];this.sort_keys=[];this.sort_colors=["#aed0ea","#aed0ea","#aed0ea"]}function tsInitOnload(){if(TSort_All==null)tsRegister();for(var e in TSort_All){tsSetTable(e);tsInit()}if(window.onload_sort_table)window.onload_sort_table()}function tsInit(){if(TSort_Data.push==null)return;var e=TSort_Data[0];var t=document.getElementById(e);var n=t.getElementsByTagName("thead")[0];if(n==null){alert("Cannot find THEAD tag!");return}var r=n.getElementsByTagName("tr");var i,s,o,u;if(r.length>1){var a=r[0].getElementsByTagName("th");if(a.length==0)a=r[0].getElementsByTagName("td");var f;var f=r[1].getElementsByTagName("th");if(f.length==0)f=r[1].getElementsByTagName("td");i=new Array;var l,c,h;u=a.length;for(l=0,c=0;l<u;l++){o=a[l];h=o.colSpan;if(h>1){while(h>0){i.push(f[c++]);h--}}else{if(o.rowSpan==1)c++;i.push(o)}}}else{i=r[0].getElementsByTagName("th");if(i.length==0)i=r[0].getElementsByTagName("td")}u=i.length;for(var s=0;s<u;s++){if(s>=TSort_Data.length-1)break;o=i[s];var p=TSort_Data[s+1].toLowerCase();if(p==null)p="";TSort_Store.sorting.push(p);if(p!=null&&p!=""){o.innerHTML="<a href='' onClick=\"tsDraw("+s+",'"+e+"'); return false\">"+o.innerHTML+'</a><b><span id="TS_'+s+"_"+e+'"></span></b>';o.style.cursor="pointer"}}var d=t.getElementsByTagName("tbody")[0];if(d==null)return;var v=d.getElementsByTagName("tr");var m=new Date;var u,g,y;for(s=0;s<v.length;s++){var b=v[s];var i=b.getElementsByTagName("td");var w=[];for(j=0;j<i.length;j++){g=i[j].innerHTML.replace(/^\s+/,"");g=g.replace(/\s+$/,"");var p=TSort_Store.sorting[j];if(p=="h"){g=g.replace(/<[^>]+>/g,"");g=g.toLowerCase()}else if(p=="s")g=g.toLowerCase();else if(p=="i"){g=parseInt(g);if(isNaN(g))g=0}else if(p=="n"){g=g.replace(/(\d)\,(?=\d\d\d)/g,"$1");g=parseInt(g);if(isNaN(g))g=0}else if(p=="c"){g=g.replace(/^\$/,"");g=g.replace(/(\d)\,(?=\d\d\d)/g,"$1");g=parseFloat(g);if(isNaN(g))g=0}else if(p=="f"){g=parseFloat(g);if(isNaN(g))g=0}else if(p=="g"){g=g.replace(/(\d)\,(?=\d\d\d)/g,"$1");g=parseFloat(g);if(isNaN(g))g=0}else if(p=="d"){if(g.match(/^\d\d\d\d\-\d\d?\-\d\d?(?: \d\d?:\d\d?:\d\d?)?$/)){y=g.split(/[\s\-:]/);g=y[3]==null?Date.UTC(y[0],y[1]-1,y[2],0,0,0,0):Date.UTC(y[0],y[1]-1,y[2],y[3],y[4],y[5],0)}else g=Date.parse(g)}w.push(g)}TSort_Store.rows.push(w);var E=b.cloneNode(true);E.tsort_row_id=s;TSort_Store.row_clones[s]=E}TSort_Store.initialized=1;if(TSort_Store.cookie){var S=document.cookie;s=S.indexOf(TSort_Store.cookie+"=");if(s!=-1){s+=TSort_Store.cookie.length+1;u=S.indexOf(";",s);g=decodeURIComponent(S.substring(s,u==-1?S.length:u));TSort_Store.initial=g==""?null:g.split(/\s*,\s*/)}}var x=TSort_Store.initial;if(x!=null){var T=typeof x;if(T=="number"||T=="string")tsDraw(x);else{for(s=x.length-1;s>=0;s--)tsDraw(x[s])}}}function tsDraw(e,t){if(t!=null)tsSetTable(t);if(TSort_Store==null||TSort_Store.initialized==0)return;var n=0;var r=TSort_Store.sort_keys;var i;var s="";if(e!=null){if(typeof e=="number")i=e;else if(typeof e=="string"&&e.match(/^\d+[ADU]$/i)){i=e.replace(/^(\d+)[ADU]$/i,"$1");s=e.replace(/^\d+([ADU])$/i,"$1").toUpperCase()}}if(i==null){i=this.tsort_col_id;if(t==null&&this.tsort_table_id!=null)tsSetTable(this.tsort_table_id)}var o=TSort_Data[0];var u=TSort_Store.sort_state[i];if(s=="U"){if(u!=null){TSort_Store.sort_state[i]=null;g=document.getElementById("TS_"+i+"_"+o);if(g!=null)g.innerHTML=""}}else if(s!=""){TSort_Store.sort_state[i]=s=="A"?true:false;r.unshift(i);n=1}else{if(u==null||u==true){TSort_Store.sort_state[i]=u==null?true:false;r.unshift(i);n=1}else{TSort_Store.sort_state[i]=null;g=document.getElementById("TS_"+i+"_"+o);if(g!=null)g.innerHTML=""}}var a=r.length;while(n<a){if(r[n]==i){r.splice(n,1);a--;break}n++}if(a>3){n=r.pop();g=document.getElementById("TS_"+n+"_"+o);if(g!=null)g.innerHTML="";TSort_Store.sort_state[n]=null}TSort_Store.row_clones.sort(tsSort);var f=document.createElement("tbody");var l=TSort_Store.row_clones;a=l.length;var c=TSort_Store.classes;if(c==null){for(n=0;n<a;n++)f.appendChild(l[n].cloneNode(true))}else{var h;var p=0;var d=c.length;for(n=0;n<a;n++){h=l[n].cloneNode(true);h.className=c[p++];if(p>=d)p=0;f.appendChild(h)}}var v=document.getElementById(o);var m=v.getElementsByTagName("tbody")[0];v.removeChild(m);v.appendChild(f);var g,y,b,w;a=r.length;var E=new Array;for(n=0;n<a;n++){i=r[n];g=document.getElementById("TS_"+i+"_"+o);if(g==null)continue;w=TSort_Store.sort_state[i]?0:1;b=TSort_Store.icons[w];g.innerHTML=b.match(/</)?b:'<font color="'+TSort_Store.sort_colors[n]+'">'+b+"</font>";E.push(i+(w?"D":"A"))}if(TSort_Store.cookie){var S=new Date;S.setTime(S.getTime()+2592e3);document.cookie=TSort_Store.cookie+"="+encodeURIComponent(E.join(","))+"; expires="+S.toGMTString()+"; path=/"}}function tsSort(e,t){var n=TSort_Store.rows[e.tsort_row_id];var r=TSort_Store.rows[t.tsort_row_id];var i=TSort_Store.sort_keys;var s=i.length;var o;var u;var a;var f;for(var l=0;l<s;l++){o=i[l];u=TSort_Store.sorting[o];var c=n[o];var h=r[o];if(c==h)continue;if(u=="i"||u=="f"||u=="d")f=c-h;else f=c<h?-1:1;a=TSort_Store.sort_state[o];return a?f:0-f}return e.tsort_row_id<t.tsort_row_id?-1:1}function tsRegister(){if(TSort_All==null)TSort_All=new Object;var e=new TSort_StoreDef;e.sort_data=TSort_Data;TSort_Data=null;if(typeof TSort_Classes!="undefined"){e.classes=TSort_Classes;TSort_Classes=null}if(typeof TSort_Initial!="undefined"){e.initial=TSort_Initial;TSort_Initial=null}if(typeof TSort_Cookie!="undefined"){e.cookie=TSort_Cookie;TSort_Cookie=null}if(typeof TSort_Icons!="undefined"){e.icons=TSort_Icons;TSort_Icons=null}if(e.icons==null)e.icons=new Array("","");if(e.sort_data!=null)TSort_All[e.sort_data[0]]=e}function tsSetTable(e){TSort_Store=TSort_All[e];if(TSort_Store==null){alert("Cannot set table '"+e+"' - table is not registered");return}TSort_Data=TSort_Store.sort_data}var TSort_Store;var TSort_All;if(window.addEventListener)window.addEventListener("load",tsInitOnload,false);else if(window.attachEvent)window.attachEvent("onload",tsInitOnload);else{if(window.onload_sort_table==null&&window.onload!=null)window.onload_sort_table=window.onload;window.onload=tsInitOnload}var TSort_Data=new Array ('report','s','i','i','f','i','i','i','i','i','i','i');</script></body></html>
