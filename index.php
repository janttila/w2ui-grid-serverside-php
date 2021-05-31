<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <title>W2UI Demo: combo/9</title>

<!-- jQuery -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
<!-- ContextMenu -->
    <script type="text/javascript" src="jQuery-contextMenu\dist\jquery.contextMenu.min.js"></script>
    <script type="text/javascript" src="jQuery-contextMenu\dist\jquery.ui.position.min.js"></script>
    <link rel="stylesheet" href="jQuery-contextMenu\dist\jquery.contextMenu.min.css">
   
<!-- W2ui -->
    <script type="text/javascript" src="w2ui\w2ui-1.5.min.js"></script>
    <link rel="stylesheet" type="text/css" href="w2ui\w2ui-1.5.min.css" />
    
</head>
<body>
<input type="hidden" id="kuk" name="kuk">
<div  id="main" style="width: 100%; height: 400px;"></div>


<script type="text/javascript">
// widget configuration
var config = {
    grid: {
        name: 'grid',
        url : 'server.php',
        show: {
            footer: true,
            toolbar: true,
            lineNumbers: true,
            expandColumn: true,
            selectColumn: false,
            contextMenu: true,
            toolbarInput: false,
            skipRecords: false,
            saveRestoreState: false   
        },
        multiSelect: false,
        limit: 10,
      multiSearch: true,
        searches: [
            { field: 'colAo', caption: 'colAo ', type: 'text' },
            { field: 'colSb', caption: 'colSb', type: 'text' },
            { field: 'colAssignedObjects', caption: 'colAssignedObjects ', type: 'text' },
            { field: 'colOwner', caption: 'colOwner', type: 'text' },
            { field: 'colTimestamp', caption: 'colTimestamp ', type: 'date' }
        ],
        columns: [
            { field: 'recid', caption: 'recid', sortable: false},
            { field: 'colAo', caption: 'colAo', searchable: 'text', sortable: true, editable: { type: 'text' }},
            { field: 'colStatus', caption: 'colStatus', size: '100%' },
            { field: 'colPrio', caption: 'colPrio', sortable: true },
            { field: 'colAssignedObjects', caption: 'colAssignedObjects', size: '200px', searchable: 'text', sortable: true },
            { field: 'colOwner', caption: 'colOwner', searchable: 'text', sortable: true },
            { field: 'colAckTime', caption: 'colAckTime', sortable: true },
            { field: 'colTimestamp', caption: 'colTimestamp', searchable: 'date', sortable: true }
           
        ],
        
        onContextMenu: function(event) {
            
            event.onComplete = function () {

                document.getElementById("kuk").value = event.originalEvent.target.outerText;
    }
            
            
        
    },
        onExpand: function (event) {
            $('#'+event.box_id).html('<div style="padding: 10px; height: 100px">Expanded content</div>');
        },
  
   
        onLoad: function(event) {
                        //Set language
                        w2utils.locale('se-se.json');
            let data = JSON.parse(event.xhr.responseText)
            data.records.forEach((rec, ind) => {
                rec.recid = 'recid-' + (this.records.length + ind)
            })
            event.xhr.responseText = data
            //hide column
            w2ui['grid'].hideColumn('recid');

        }

        
    }
};

function refreshGrid(auto) {
    w2ui.grid.autoLoad = auto;
    w2ui.grid.skip(0);
}

$(function () {
    $('#main').w2grid(config.grid);
});
</script>

<script> // ContextMenu

$(function() {
        $.contextMenu({
            selector: '.w2ui-grid-records', 
            callback: function(key, options) {
                var m = "clicked: " + key;
                //window.console && console.log(m) || alert(m); 
            },
            items: {
                "edit": {name: "Edit", icon: "edit"},
                "cut": {name: "Cut", icon: "cut"},
                "copy": {name: "copy", icon : function(){
                    copyToClipboard(document.getElementById("kuk").value);
                    return 'context-menu-icon context-menu-icon-copy';
                }},
                "paste": {name: "Paste", icon: "paste"},
                "delete": {name: "Delete", icon: "delete"},
                "sep1": "---------",
                "quit": {name: "Quit", icon: function(){
                    return 'context-menu-icon context-menu-icon-quit';
                }}
            }
        });
        
        $('.context-menu-one').on('click', function(e){
            console.log('clicked', this);
        })    
    });

</script>

<script>

function copyToClipboard(text) {
    var dummy = document.createElement("textarea");
    // to avoid breaking orgain page when copying more words
    // cant copy when adding below this code
    // dummy.style.display = 'none'
    document.body.appendChild(dummy);
    //Be careful if you use texarea. setAttribute('value', value), which works with "input" does not work with "textarea". – Eduard
    dummy.value = text;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
}

</script>
</body>
</html>