<html>
    <head>
        <link rel="stylesheet" href="style.css" type="text/css" />
    </head>
    <body>
        <div id="site-body">
            <ul id="tabs">
                <li><a class ="tabs current" id="tab-report">Report</a></li>
                <li><a class="tabs" id="tab-stats">Stats</a></li>
            </ul>

            <div id="tabs-content">
                <div id="view-report" class="show">

                    <form id="make-report">
                        <label for="from-date">From</label>
                        <input type="text" name="end" id="from-date">
                        <button type="submit">Show</button>
                    </form>
                    
                    <form id="upload">
                        <label for="file">xml file:</label>
                        <input type="file" name="file" id="file">
                        <input type="submit" name="submit" value="Submit">
                        <div id="ul-err"></div>
                    </form>

                    <form id="user-edit">
                        <div>
                            <input type="text" id="edit-name" name="name" disabled/><input type="text" id="edit-id" name="id" readonly/>
                        </div>
                        <div>
                            <textarea id="notes" name="notes" form="user-edit"></textarea>
                        </div>
                        <button type="submit">Update</button>
                    </form>

                    <div id="report-content"></div> <!-- report-content end -->
                </div> <!-- view-report end -->

                <div id="view-stats" class="hide">
                    <form id="add-stats">
                        <label for="stats">Stats</label><br />
                        <textarea name="stats" form="add-stats"></textarea>
                        <span id="stats-info">
                            Example:<br />
                            name: Example [12345]<br />
                            level:&nbsp;&nbsp;&nbsp;&nbsp;55<br />
                            Str:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;600,602,231.4632<br />
                            defence:100,166,988<br /><br />
                            name: example2 his id is= 431<br />
                            lvl:1<br />
                            strength:10000<br />
                            sped:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;231.4632<br />
                            DEFasgdjhdgr:&nbsp;30<br /><br />
                            Only requied name and id separated by at least one space.<br />  
                            Rest stats are optional. Recognized prefixes:<br />
                            name:<br /> 
                            str*:<br />
                            def*:<br />
                            spd:/sped:/speed:<br />
                            dex*:<br />
                            total:<br /><br />                          
                            * = following any characters or nothing up to colon (:).<br />
                            Each profile separated by new line, like in the example.
                        </span>
                        <br />
                        <button type="submit">Submit</button>
                    </form>
                    <div id="status-rslt"></div>
                </div> <!-- view-stats end -->

            </div> <!-- tabs-content end -->

        </div> <!-- site-body end -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="jquery-ui-1.10.1.custom.min.js"></script>
        <script src="script.js"></script>
    </body>
</html>