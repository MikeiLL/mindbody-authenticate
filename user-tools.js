import {choc, set_content, on, DOM} from "https://rosuav.github.io/choc/factory.js";
const {FORM, INPUT, LABEL} = choc; //autoimport

(function ($) {
    $(document).ready(function ($) {
      // Shortcode atts for current page from parent plugin.
      const atts = mz_mindbody_schedule.atts;

      /**
       * State will store and track status
       */
      const mz_mbo_state = {

        logged_in: user_tools.logged_this_studio,
        action: undefined,
        target: undefined,
        siteID: user_tools.siteID,
        nonce: undefined,
        client_first_name: user_tools.client_first_name,
        client_last_name: user_tools.client_last_name,
        location: undefined,
        classID: undefined,
        className: undefined,
        staffName: undefined,
        classTime: undefined,
        class_title: undefined,
        content: undefined,
        spinner: '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>',
        wrapper: undefined,
        content_wrapper: '<div class="modal__content" id="signupModalContent"></div>',
        header: undefined,
        signup_button: undefined,
        message: undefined,
        base_url: window.location.origin + "/wp-json/mindbody-auth/v1/",

        initialize: function (target) {
            this.target = $(target).attr("href");
            this.siteID = $(target).attr('data-siteID');
            this.nonce = $(target).attr("data-nonce");
            this.location = $(target).attr("data-location");
            this.classID = $(target).attr("data-classID");
            this.className = $(target).attr("data-className");
            this.staffName = $(target).attr("data-staffName");
            this.classTime = $(target).attr("data-time");
            this.class_title = '<h2>' + this.className + ' ' + mz_mindbody_schedule.with + ' ' + this.staffName + '</h2><h3>' + this.classTime + '</h3><hr/>';
            this.header = '<div class="modal__header" id="modalHeader"><h1>'+mz_mindbody_schedule.signup_heading+'</h1>'+this.class_title+'</div>';
            this.signup_button = '<button class="btn btn-primary" data-nonce="'+this.nonce+'" data-location="'+this.location+'" data-classID="'+this.classID+'" id="signUpForClass">' + user_tools.confirm_signup + '</button>';
          }
      };

      console.log(mz_mbo_state);
      window.mz_mbo_state = mz_mbo_state;
      window.SESSION = JSON.parse(user_tools.SESSION);

      function get_footer(state) {
        return '<div class="modal__footer" id="signupModalFooter">\n' +
          '    <a class="btn btn-primary" data-nonce="' + user_tools.nonce + '" id="MBOSchedule" target="_blank">My Classes</a>\n' +
          '  <div class="user-info">\n' +
          (state.client_first_name ? '    <span>' + state.client_first_name + ' ' + state.client_last_name + '</span> \n' : "<span></span>") +
          '    <svg viewBox="0 0 24 24" fill="none" data-nonce="' + user_tools.nonce + '" id="MBOLogout" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M4 12H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M8 7L3 12L8 17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M21 3L21 21" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>\n' +
          '  </div>\n' +
          '</div>\n';
      }

      /**
       * Event listeners
       */
      window.addEventListener('authenticated', function (e) {
        $.colorbox({html: '<h1 id="registerheading"></h1><div id="registernotice"></div>'});
        mz_mbo_state.logged_in = true;
        mz_mbo_state.action = 'login';
        console.log(e.detail);
        mz_mbo_state.message = 'Welcome back!';
        mz_mbo_state.client_first_name = e.detail.firstName;
        mz_mbo_state.client_last_name = e.detail.lastName;
        console.log("authenticated mz_mbo_state", mz_mbo_state, mz_mbo_state.message);
        render_mbo_modal();
        render_mbo_modal_activity();
      });
      window.addEventListener('need_to_register', function () {
        // Build up our form
        let form = '<form id="mzStudioRegisterForm" method="post"><fieldset><legend>Please submit all fields</legend>';
        JSON.parse(user_tools.required_fields).forEach(function (field) {
          // Parse for display, addiing space between words.
          form += `<label><span>${field.replaceAll(/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z]|(?<=[A-Z])[0-9_])/g, ' $1')}</span> `;
          // Phone needs to have _number append to form field name. Underscore added in subsequent step.
          if (field.toLowerCase().includes('phone')) {
            field += 'Number';
          }
          // Parse for form fields for submission.
          form += `<input type="text" name="${field.replaceAll(/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z]|(?<=[A-Z])[0-9_])/g, '_$1').toLowerCase()}" required>`;
          form += `</label > `;
        });
        form += `</fieldset><input type="submit" value="Submit">`;
        form += `</form>`;
        $.colorbox({html:'<h1 id="registerheading">Looks like you need to register with our studio.</h1><div id="registernotice"></div>'+form});
      });

      on('submit', '#mzStudioRegisterForm', function (event) {
        event.preventDefault();
        let form = event.target;
        let data = new FormData(form);
        fetch(mz_mbo_state.base_url + `registeruser?`, {method: 'POST', body: data, credentials: 'include'})
          .then(r => r.json())
          .then(json => {
            if (json.success) {
              $.colorbox({html:'<h1>Thanks for registering with our studio. You can now sign up for some classes.</h1>'+form});
            } else {
              console.log("error", json);
              DOM('#registernotice').innerHTML = "Something went wrong with your registration. Here's what we know: " + json.error;
            }
          });
      });

      /*
      * Define the modal container state which changes depending on login state
      */
      function render_mbo_modal() {
        console.log("render_mbo_modal", mz_mbo_state.message);
        $.colorbox({html:'<h1 id="registerheading"></h1><div id="registernotice"></div>'});
        var message = (mz_mbo_state.message ? '<p>'+mz_mbo_state.message+'</p>' : '');
        mz_mbo_state.wrapper = '<div class="modal__wrapper" id="signupModalWrapper">';
        if (mz_mbo_state.logged_in + "" === "true"){
          mz_mbo_state.wrapper += mz_mbo_state.header;
          mz_mbo_state.wrapper += '<div class="modal__content" id="signupModalContent">'+mz_mbo_state.signup_button+'</div>';
          mz_mbo_state.wrapper += get_footer(mz_mbo_state);
        } else {
            mz_mbo_state.wrapper += mz_mbo_state.header;
            mz_mbo_state.wrapper += '<div class="modal__content" id="signupModalContent">'+message+mz_mbo_state.login_form+'</div>';
        }
        mz_mbo_state.wrapper += '</div>';
        if ($('#cboxLoadedContent')) {
            $('#cboxLoadedContent').html(mz_mbo_state.wrapper);
        }
        mz_mbo_state.message = undefined;
      }

    /*
    * Render inner content of modal based on state
    */
    function render_mbo_modal_activity(){
        // Clear content and content wrapper
        mz_mbo_state.content = '';
        $('#signupModalContent').html = '';
        if (mz_mbo_state.action == 'processing'){
          mz_mbo_state.content += mz_mbo_state.spinner;
        } else if (mz_mbo_state.action == 'login_failed') {
          mz_mbo_state.content += mz_mbo_state.message;
        } else if (mz_mbo_state.action == 'logout') {
          console.log("State action is logout.");
          mz_mbo_state.content += mz_mbo_state.message;
          mz_mbo_state.logged_in = false;
          user_tools.logged_this_studio = false;
          user_tools.AuthorizedMBO = false;
          sessionStorage.removeItem("MindbodyAuth");
          setTimeout($.colorbox.close, 3000);
        } else if (mz_mbo_state.action == 'login') {
          mz_mbo_state.content += mz_mbo_state.message;
        } else if (mz_mbo_state.action == 'create_account') {
          mz_mbo_state.content += mz_mbo_state.message;
        } else if (mz_mbo_state.action == 'error') {
          mz_mbo_state.content += mz_mbo_state.message;
        } else {
          // login, sign_up_form
          mz_mbo_state.content += mz_mbo_state.message;
        }
        if ($('#signupModalContent')) {
          $('#signupModalContent').html(mz_mbo_state.content);
        }
        mz_mbo_state.message = undefined;
      }

      /**
       * Continually Check if Client is Logged in and Update Status
       */
      // TODO reinstate: setInterval(mz_mbo_check_client_logged, 5000);
      function mz_mbo_check_client_logged( )
      {
          //this will repeat every 5 seconds
          $.ajax({
              dataType: 'json',
              url: mz_mindbody_schedule.ajaxurl,
              data: {action: 'mz_check_client_logged', nonce: 'mz_check_client_logged'},
              success: function(json) {
                  if (json.type == "success") {
                      mz_mbo_state.logged_in = (json.message == 1 ? true : false);
                  }
              } // ./ Ajax Success
          }); // End Ajax
      }

      /**
       * Initial Modal Window to Register for a Class
       *
       * Also leads to options to login and sign-up with MBO
       */
      $(document).on('click', "a[data-target=mzSignUpModal]", function (ev) {
        ev.preventDefault();
        console.log("clicked signup modal");
        console.log("mz_mbo_state.logged_in", mz_mbo_state.logged_in);
        if (mz_mbo_state.logged_in + "" !== "true") {
          console.log("Opening MBO Login")
          window.open(user_tools.mbo_oauth_url, '_blank', 'location=yes,height=570,width=520,scrollbars=yes,status=yes');
        } else {
            mz_mbo_state.classID = ev.target.dataset['classid'];
            mz_mbo_state.initialize(this);
            render_mbo_modal();
          }

      });

      /**
       * Logout of MBO
       *
       *
       */
      $(document).on('click', "#MBOLogout", function (ev) {
          ev.preventDefault();
          var nonce = $(this).attr("data-nonce");
          console.log("Logging out");
          $.ajax({
              dataType: 'json',
              url: mz_mindbody_schedule.ajaxurl,
              data: {action: 'mz_client_logout', nonce: nonce},
              beforeSend: function() {
                  mz_mbo_state.action = 'processing';
                  render_mbo_modal_activity();
              },
              success: function(json) {
                  if (json.type == "success") {
                      mz_mbo_state.logged_in = false;
                      mz_mbo_state.action = 'logout';
                      mz_mbo_state.message = json.message;
                      render_mbo_modal_activity();
                  } else {
                      mz_mbo_state.action = 'logout_failed';
                      mz_mbo_state.message = json.message;
                      render_mbo_modal_activity();
                  }
              } // ./ Ajax Success
          }) // End Ajax
              .fail(function (json) {
                  mz_mbo_state.message = 'ERROR LOGGING OUT';
                  render_mbo_modal_activity();
                  console.log(json);
              }); // End Fail
      });

      /**
       * Display MBO Account Registration form within Sign-Up Modal
       *
       * Deprecated?????
       */
      $(document).on('click', "a#createMBOAccount", function (ev) {
          ev.preventDefault();
          var target = $(this).attr("href");
          var nonce = $(this).attr("data-nonce");
          var classID = $(this).attr("data-classID");
          $.ajax({
              type: "GET",
              dataType: 'json',
              url: mz_mindbody_schedule.ajaxurl,
              data: {action: 'mz_generate_signup_form', nonce: nonce, classID: classID},
              beforeSend: function() {
                  mz_mbo_state.action = 'processing';
                  render_mbo_modal_activity();
              },
              success: function(json) {
                  if (json.type == "success") {
                      mz_mbo_state.logged_in = true;
                      mz_mbo_state.action = 'sign_up_form';
                      mz_mbo_state.message = json.message;
                      render_mbo_modal_activity();
                  } else {
                      mz_mbo_state.action = 'error';
                      mz_mbo_state.message = json.message;
                      render_mbo_modal_activity();
                  }
              } // ./ Ajax Success
          }) // End Ajax
              .fail(function (json) {
                  mz_mbo_state.message = 'ERROR GENERATING THE SIGN-UP FORM';
                  render_mbo_modal_activity();
                  console.log(json);
              }); // End Fail

      });



      /**
       * Create MBO Account and display Confirmation
       *
       *
       */
      $(document).on('submit', 'form[id="mzSignUp"]', function (ev) {
          ev.preventDefault();
          var target = $(this).attr("href");
          var form = $(this);
          var nonce = $(this).attr("data-nonce");
          var classID = $(this).attr("data-classID");
          var formData = form.serializeArray();
          $.ajax({
              type: "GET",
              dataType: 'json',
              url: mz_mindbody_schedule.ajaxurl,
              data: {action: 'mz_create_mbo_account', nonce: formData.nonce, classID: formData.classID, form: form.serialize()},
              beforeSend: function() {
                  mz_mbo_state.action = 'processing';
                  render_mbo_modal_activity();
              },
              success: function (json) {
                  if (json.type == "success") {
                      mz_mbo_state.logged_in = true;
                      mz_mbo_state.action = 'create_account';
                      mz_mbo_state.message = json.message;
                      render_mbo_modal_activity();
                  } else {
                      mz_mbo_state.action = 'error';
                      mz_mbo_state.message = json.message;
                      render_mbo_modal_activity();
                  }
              } // ./ Ajax Success
          }) // End Ajax
              .fail(function (json) {
                  mz_mbo_state.message = 'ERROR CREATING ACCOUNT';
                  render_mbo_modal_activity();
                  console.log(json);
              }); // End Fail

      });

      /**
       * Register for a class
       */
      $(document).on('click', '#signUpForClass', function (ev) {
          ev.preventDefault();

          var nonce = $(this).attr("data-nonce");

          $.ajax({
              type: "GET",
              dataType: 'json',
              url: mz_mindbody_schedule.ajaxurl,
              context: this,
              data: {
                  action: 'mz_register_for_class',
                  nonce: nonce,
                  siteID: mz_mbo_state.siteID,
                  classID: mz_mbo_state.classID,
                  location: mz_mbo_state.location
              },
              beforeSend: function() {
                  mz_mbo_state.action = 'processing';
                  render_mbo_modal_activity();
              },
            success: function (json) {
                console.log(json);
                  if (json.type == "success") {
                      mz_mbo_state.action = 'register';
                      mz_mbo_state.message = json.message;
                      render_mbo_modal_activity();
                  } else {
                      mz_mbo_state.action = 'error';
                      mz_mbo_state.message = 'ERROR REGISTERING FOR CLASS. ' + json.message;
                      render_mbo_modal_activity();
                  }
              } // ./ Ajax Success
          }) // End Ajax
              .fail(function (json) {
                console.log(json);
                  mz_mbo_state.message = 'ERROR REGISTERING FOR CLASS';
                  render_mbo_modal_activity();
                  console.log(json);
              }); // End Fail
      });

      /**
       * Display Client Schedule within Sign-Up Modal
       *
       *
       */
      $(document).on('click', "a#MBOSchedule", function (ev) {
          console.log("mz_mindbody_schedule.ajaxurl", mz_mindbody_schedule.ajaxurl);
            ev.preventDefault();
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: mz_mindbody_schedule.ajaxurl,
                data: {action: 'mz_display_client_schedule', nonce: user_tools.nonce, location: mz_mbo_state.location, siteID: mz_mbo_state.siteID},
                beforeSend: function() {
                    mz_mbo_state.action = 'processing';
                    render_mbo_modal_activity();
                },
                success: function (json) {
                    if (json.type == "success") {
                        mz_mbo_state.action = 'display_schedule';
                        mz_mbo_state.message = json.message;
                        render_mbo_modal_activity();
                    } else {
                        mz_mbo_state.action = 'error';
                        mz_mbo_state.message = 'ERROR RETRIEVING YOUR SCHEDULE. ' + json.message;
                        render_mbo_modal_activity();
                    }
                } // ./ Ajax Success
            }) // End Ajax
                .fail(function (json) {
                    mz_mbo_state.message = 'ERROR RETRIEVING YOUR SCHEDULE';
                    render_mbo_modal_activity();
                    console.log(json);
                }); // End Fail

        });

    }); // End document ready
})(jQuery);
