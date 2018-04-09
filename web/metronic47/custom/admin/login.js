var Login = function() {

    var handleLogin = function() {

        $('.login-form').validate({
            // errorElement: 'span', //default input error message container
            // errorClass: 'help-block', // default input error message class
            focusInvalid: true, // do not focus the last invalid input
            rules: {
                username: {
                    required: true
                },
                password: {
                    required: true
                },
                remember: {
                    required: false
                }
            },


            invalidHandler: function(event, validator) { //display error alert on form submit
            },

            highlight: function(element) { // hightlight error inputs
                $(element).closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label.closest('.form-group').removeClass('has-error');
                label.remove();
            },

            errorPlacement: function(error, element) {
                // this.showError(error);
            },

            submitHandler: function(form) {
                var validator = this;
                $.ajax({
                    dataType: 'json',
                    type: 'post',
                    url: '',
                    data: $(form).serialize(),
                    complete: function(XHR, TS) {
                        var url = XHR.getResponseHeader('X-Redirect');
                        if(url) {
                            window.location.href = url;
                        }
                    },
                    success: function(data, textStatus, jqXHR){
                        if(data.status) {
                            window.location.reload();
                        } else {
                            validator.showErrors({'LoginForm[username]':data.msg});
                        }
                    }
                });
            },
            showErrors: function(errorMap,errorList) {
                $.each( errorMap, function(i,v){
                    if(v) {
                        $('.alert-danger > span').text(v);
                        $('.alert-danger', $('.login-form')).show();
                    }
                });
                this.defaultShowErrors();
            },
        });

        $('.login-form input').keypress(function(e) {
            if (e.which == 13) {
                if ($('.login-form').validate().form()) {
                    $('.login-form').submit(); //form validation success, call ajax form submit
                }
                return false;
            }
        });

        $('.forget-form input').keypress(function(e) {
            if (e.which == 13) {
                if ($('.forget-form').validate().form()) {
                    $('.forget-form').submit();
                }
                return false;
            }
        });

        $('#forget-password').click(function(){
            $('.login-form').hide();
            $('.forget-form').show();
        });

        $('#back-btn').click(function(){
            $('.login-form').show();
            $('.forget-form').hide();
        });
    }




    return {
        //main function to initiate the module
        init: function() {

            handleLogin();



            $('.forget-form').hide();

        }

    };

}();

jQuery(document).ready(function() {
    Login.init();
});
