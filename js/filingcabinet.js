    // ID of the file last added
    // used to do sequence linking
    var fileLastAdded = null;
    
    // make a recursive function to add the files one at a time in orderof appearance
    // they need to be added thus so that the sequence linking will work
    function addFiles(uploaded) {
        // exit condition is empty array
        if (uploaded.length == 0) {
            // on exit hide all elements for sucessfully added files
            $('#file-uploader').find('.added').hide(400, function(){
                $(this).remove();
                // hide bits and pieces that are no longer necessary
                if ($('#file-uploader').find('.qq-file-data').length == 0) {
                    $('#file-uploader').find('.qq-upload-list-head, .qq-upload-list-shortcuts').css('display', 'none');
                }
                // if there is at least one element left disable the sequence checkbox on the first
                else {
                    $('#file-uploader').find('.qq-file-data:first').attr('disabled', 'disabled');
                }
                if ($('#file-uploader').find('.qq-upload-public-checkbox:enabled').length < 2) {
                    $('#file-uploader').find('.qq-upload-public-all').css('display', 'none');
                }
                if ($('#file-uploader').find('.qq-upload-sequence-checkbox:enabled').length < 2) {
                    $('#file-uploader').find('.qq-upload-sequence-all').css('display', 'none');
                }
            });
            fileLastAdded = null;
            // if there are no more uploaded files to add we can hide the 'add' button
            if ($('#file-uploader').find('.qq-upload-success').not('.added').length == 0) {
                $('#add_to_cabinet').hide(400);
            }
            $('#add_to_cabinet').removeClass('loading');
        } else {
            fileElement = uploaded.first();
            if (fileElement.hasClass('qq-upload-fail')) {
                    // delete rows where the upload failed
                    fileElement.remove();
                    fileLastAdded = null;
            } else if (fileElement.hasClass('qq-upload-success')) {
                    var data = {
                        filename: fileElement.find('.qq-upload-full-file').val(),
                        rename: fileElement.find('.qq-upload-rename-input').val(),
                        labels: fileElement.find('.qq-upload-label-input').val(),
                        pub: fileElement.find('.qq-upload-public-checkbox:checked').length
                        
                    };
                    if (fileLastAdded != null && fileElement.find('.qq-upload-sequence-checkbox:checked').length > 0) {
                        data.sequence = fileLastAdded;
                    }
                    $('#add-file-results').append('<li></li>')
                    $('#add-file-results li:last').load('add-file.php', data, function() {
                        if ($('#add-file-results').children().last().html().indexOf('<span class="file_id">') != -1) {
                            fileLastAdded = $('#add-file-results').children().last().find('.file_id').html();
                            fileElement.addClass('added');
                        } else {
                            fileLastAdded = null;
                        }
                        addFiles(uploaded.slice(1)); // recursive call
                    });
            }
            // if it has neither the fail nor success class then it hasn't finished uploading
            // in this case just leave it.
            fileElement.removeClass('attempt-add');
        }
    }

    $(function() {
        $("#label_select").autocomplete({
            source: $('#autocomplete_src').html(),
            minLength: 2,
            select: function(event, ui) {
                $("#label_select").val(ui.item.value);
                $("#label_select_form").submit();
            }
        });
    });
    
    $(document).ready(function(){
        $.ajaxSetup ({  
            cache: false  
        });  
    
        $('.begin_hidden_js').hide();
        $('.main_toggle_panel').addClass('reduced_margin');
        
        if ($('#login_status').html().indexOf('is active') != -1) { // user has logged in
            $('#login_content_A').css('display', 'block');
            var username = $('#login_status').html().substring($('#login_status').html().indexOf('<span class="uname">'),$('#login_status').html().indexOf('</span>'))
            $('#info').html('Welcome back ' + username);
        } else if ($('#login_status').html().indexOf('not active') != -1) { // user has not yet activated account
            $('#upload_panel').hide();
            $('#login_content_B').css('display', 'block');
        } else { // user is not logged in
            $('#upload_panel').hide();
            $('#login_content_C').css('display', 'block');
        } 
  
        var uploader = new qq.FileUploader({
            // pass the dom node (ex. $(selector)[0] for jQuery users)
            element: document.getElementById('file-uploader'),
            // path to server-side upload script
            action: 'qquploader.php',
            onSubmit: function(id, fileName) {
                // disable sequence checkbox for first item, because there's nothing before it to link to
                $('#file-uploader').find('.qq-file-data:first').find('.qq-upload-sequence-checkbox').attr('disabled', 'disabled');
                // display bits and pieces when necessary
                if ($('#file-uploader').find('.qq-file-data').length > 0) {
                    $('#file-uploader').find('.qq-upload-list-head').css('display', 'block');
                    $('#file-uploader').find('.qq-upload-list-shortcuts').css('display', 'block');
                }
                if ($('#file-uploader').find('.qq-upload-public-checkbox:enabled').length > 1) {
                    $('#file-uploader').find('.qq-upload-public-all').css('display', 'inline');
                }
                if ($('#file-uploader').find('.qq-upload-sequence-checkbox:enabled').length > 1) {
                    $('#file-uploader').find('.qq-upload-sequence-all').css('display', 'inline');
                }
            },
            onComplete: function(id, fileName, responseJSON) {
                if (responseJSON['success']) {
                    $('#add_to_cabinet').show(400);
                }
            }
        });
        
        //$('.button').button();
        
        $("#login_title").click(function(){
            $('#login_content').toggle(400);
            $('#login_panel').toggleClass('reduced_margin');
        });
        $("#register_title").click(function(){
            $('#register_content').slideToggle(400);
        });
        $("#upload_title").click(function(){
            $('#upload_content').toggle(400);
            $('#upload_panel').toggleClass('reduced_margin');
        });
        $("#common_title").click(function(){
            $('#common_content').slideToggle(400);
        });
        $("#pop_title").click(function(){
            $('#pop_content').slideToggle(400);
        });
        $("#new_title").click(function(){
            $('#new_content').slideToggle(400);
        });
        
        $("#submit_login").click(function(){
            $('#submit_login').addClass('loading');
            $("#info").load("login.php", {uname: $("#uname").val(), pwd: $("#pwd").val()}, function() {
                if ($('#info').html().indexOf('succeeded') != -1)
                {
                    $('#login_content').hide(400, function(){
                        $('#login_panel').addClass('reduced_margin');
                        $('#login_content_C').css('display', 'none');
                        // extract the username from the info div to put in the login_title div
                        $('#login_title').html($('#info').html().substring($('#info').html().indexOf('<span class="uname">'),$('#info').html().indexOf('</span>'))); 
                        if ($('#info').html().indexOf('not yet been activated') != -1)
                        {
                            $('#login_content_B').css('display', 'block');
                        } else {
                            $('#login_content_A').css('display', 'block');
                            $('#upload_panel').show(400);
                        }
                    });
                } else {
                    $('#uname').val('');
                    $('#pwd').val('');
                }
                $('#submit_login').removeClass('loading');
            });
        });
        
        $("#reg_submit").click(function(){
            $('#reg_submit').addClass('loading');
            $("#info").load("register.php", {uname: $("#reg_uname").val(), pwd: $("#reg_pwd").val(), email: $("#reg_email").val()}, function() {
                $('#reg_uname').val('');
                $('#reg_pwd').val('');
                $('#reg_email').val('');
                if ($('#info').html().indexOf('registered') != -1)
                {
                    $('#register_content').slideToggle(400);
                }
                $('#reg_submit').removeClass('loading'); 
            });
        });
        
        $("#submit_logout_A").click(function() {
            $('#submit_logout_A').addClass('loading');
            $("#info").load("login.php", {logout: 1}, function() {
                $('#upload_panel').hide(400);
                $('#login_content').hide(400, function() {
                  $('#login_panel').addClass('reduced_margin');
                  $('#login_content_A').css('display', 'none');
                  $('#login_title').html('Login');
                  $('#login_content_C').css('display', 'block');
                });
                $('#submit_logout_A').removeClass('loading');
            });
        }); 
        
        $("#submit_logout_B").click(function() {
            $('#submit_logout_B').addClass('loading');
            $("#info").load("login.php", {logout: 1}, function() {
                $('#login_content').hide(400, function() {
                  $('#login_panel').addClass('reduced_margin');
                  $('#login_content_B').css('display', 'none');
                  $('#login_title').html('Login');
                  $('#login_content_C').css('display', 'block');
                });
                $('#submit_logout_B').removeClass('loading');
            });
        });
        
        $('#file-uploader').find('.qq-upload-public-all').click(function() {
            $('#file-uploader').find('.qq-upload-public-checkbox:enabled').attr('checked', $('#file-uploader').find('.qq-upload-public-all').is(':checked'));
        });
        
        $('#file-uploader').find('.qq-upload-sequence-all').click(function() {
            $('#file-uploader').find('.qq-upload-sequence-checkbox:enabled').attr('checked', $('#file-uploader').find('.qq-upload-sequence-all').is(':checked'));
        });
        
        $('#add_to_cabinet').click(function() {
            $('#add_to_cabinet').addClass('loading');
            $('#info').html('<ul id="add-file-results"></ul>');
            // send the elements in the upload list to the function to add them to the cabinet
            // exclude file elements already added
            addFiles($('#file-uploader').find('.qq-file-data').not('.attempt-add'));
            $('#file-uploader').find('.qq-file-data').not('.attempt-add').addClass('attempt-add');
        }); 
        
    });
