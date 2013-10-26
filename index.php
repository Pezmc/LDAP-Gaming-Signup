<?php

/* Website Plan */
/* Snazzy looking form, that allows typing of words, if it is a name it tries auto complete
    - Must have skip
/* If it is an ID number it loads info on that person straight to the form on the next page

*/

?>
<html>
<head>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="scripts/cardreader.js"></script>
    <link rel="stylesheet" href="styles/main.css" type="text/css" />
    <meta name="viewport" content="width=550,user-scalable=no" />
    <title>Gaming Society Signup</title>
    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QAAAAAAAD5Q7t/AAACTElEQVQ4y42TS0hUYRiGn/+cMzMMY5SSaSrlLvDCFBpBGpRQTWW0awiKNhoYkbQRLSSSDLVNQwupRaCEl6YIpSKIIEKNUbBFFo3aQiFvqIhzPXPOzGkxOR4vC1/4Fz/f+73f+34/v+A/WpsbjYP5BwgFgiwtr1B394FgB5AARn2fDaeziMX5WSLhAKWlTpqbGoydCIgP/d2GJEnEYhodnV0AXKw8R1bWPoZHRrnf1LrBya8fQynhguLjQsrM3MvkuJ+Ozi5WYwqrMYUebx9/JvyUlDi5favaMDcPDvhYOwBKKBROqcuyIB43cNityaIs4zpTQXFhgVFWfozBAR/VNXfEpgg9BkAgEOCV9y0AN2uqANA0DYB4IsHM37ktzQACoPvlcyN9z24mx/3JbEVFqGoMIdb5uq5js1o4ff7yBhEJ4MrVG8JV6RbZOXlk5+QRiai0d/RjUZQUUVEUZIuVbR2Y8brnhZGW5iCRSPDbP0HFyRPMzS8kyUJgsVj45hvhXuMjsa0AwPu+bgPA4bCj63F+TswwPbeELEmEIhrpu+wEFqfwPH2WDFlfV5t6qpY2jzDfL5w9hbd/GO+R5EJnnxwGoO36ElpwAcXcaHbR0uYRH9/1Gm8+fUeyW1FUDd22Xg9HVcpKncklrsE8ub6u1vjydQhXeQE2q8R0+RgzR8cIqO3JE4wwu6xtFDBPX3PkqnQLRZa3cNSojk2Jr0fYPN1MLjyUT0NvgmAohnpJkJthJzy1gtt9TezoywI8fOwxAKJRldz9GdRUVQmAf4HJ7uylV8I1AAAAAElFTkSuQmCC" rel="icon" type="image/x-icon" />
    <script type="text/javascript">
    $(document).ready(function() {
      $("#noscript").remove();
    
      var input = $("#mainInput");
      var dropdown = $("#dropdown");
      var timeout;
      var selected_dropdown_item, current_dropdown_ul;
      
      // Keys "enum"
      var KEY = {
          BACKSPACE: 8,
          TAB: 9,
          ENTER: 13,
          CTRL: 17,
          ALT: 18,
          ESCAPE: 27,
          SPACE: 32,
          PAGE_UP: 33,
          PAGE_DOWN: 34,
          END: 35,
          HOME: 36,
          LEFT: 37,
          UP: 38,
          RIGHT: 39,
          DOWN: 40,
          NUMPAD_ENTER: 108,
          COMMA: 188,
          META: 91,
          SHIFT: 16
      };
    
      input.keydown(function(event) {
              switch(event.keyCode) {
                  case KEY.ESCAPE:
                      hide_dropdown();
                    break;
                  case KEY.UP:
                  case KEY.DOWN:
                      var dropdown_item = null;

                      if(event.keyCode === KEY.DOWN) {
                          if(!selected_dropdown_item) {
                            dropdown_item = $(current_dropdown_ul).children(":first");
                          } else {
                            dropdown_item = $(selected_dropdown_item).next();
                          }
                          
                      } else {
                          dropdown_item = $(selected_dropdown_item).prev();
                      }

                      if(dropdown_item.length) {
                          select_dropdown_item(dropdown_item);
                      }
                      
                      return false;
                    break;
                  case KEY.ENTER:
                    
                    if(selected_dropdown_item) {
                      second_page($(selected_dropdown_item));
                    }
                  
                    return false;
                  break;
                  case KEY.TAB:
                  case KEY.ALT:
                  case KEY.CTRL:
                  case KEY.META:
                  case KEY.SHIFT:
                  case KEY.LEFT:
                  case KEY.RIGHT:
                      //Currently do nothing there are here so they don't start the timeout
                    break;
                  default: 
                      if(is_printable_character(event.keyCode)) {
                        setTimeout(function(){search();}, 5);
                      }
                    break;
              }
            })
           .blur(function () {
              hide_dropdown();
            })
           .focus(function () {
              if(input.val() && input.val() != "") {
                search();
              } else {
                show_dropdown_hint();
              }
            });
      
      function search() {
        var query = input.val().toLowerCase();
        
        //This is part of the reset countdown
        clearTimeout(countdown);
        
        //This is to tidy shit up and prevent person sharing
        $('#page2 :input').clearForm();
        $("#imessageERROR").slideUp(500);
        $("#page2").slideUp(500);
        $("#page3").slideUp(500);
        $("#loader").fadeOut(500);
        
        if(query && query.length) {
        
          if(query.length >= 3) {
              show_dropdown_searching();
              
              clearTimeout(timeout);
  
                timeout = setTimeout(function(){
                    run_search(query);
                }, 500);
          } else {
              hide_dropdown();
              clearTimeout(timeout);
              timeout = setTimeout(function(){
                    show_dropdown_tooshort()
                }, 500);
          }
        }
      }
      
      function run_search(query) {
        selected_dropdown_item = null;
        $.getJSON('ldap.php?query='+query, function(data) {
          
          if(input.val().toLowerCase() === query) {
            
            //Possible status //REQUEST_DENIED, OVER_LIMIT, INVALID_REQUEST, HIT_LIMIT, OK
            if(data.status=="OK"||data.status=="HIT_LIMIT") {
              if(data.count==1&&!isNaN(query)) {
                populate_dropdown(query, data.results);
                second_page($(current_dropdown_ul).children(":first"));
              } else if(data.count>=1) {
                populate_dropdown(query, data.results);
              } else {
                show_dropdown_noresults();
              }
            } else if(data.status=="ZERO_RESULTS") {
              show_dropdown_noresults();
            } else {
              show_dropdown_failed();
            }
          }
        
        })
        .error(function(){ hide_dropdown(); });
      }
      
      function populate_dropdown(query, results) {
        if(results && results.length) {
            dropdown.empty();
            
            var dropdown_ul = $("<ul>")
                .appendTo(dropdown)
                .hide()
                .mouseover(function (event) {
                    select_dropdown_item($(event.target).closest("li"));
                })
                .mousedown(function (event) {
                    second_page($(event.target).closest("li"));
                    return false;
                });
            
            $.each(results, function(index, value) {
                
                //Convert ajax results to a person
                person = {
                  full_name: ((value.cn && value.cn[0]) ? value.cn[0] : false),
                  //If first name given use that, else if fill name given split and use first half, if exists else false
                  first_name: ((value.givenname && value.givenname[0]) ?
                                            value.givenname[0]
                                          :
                                            ((value.cn && value.cn[0]) ?
                                              (value.cn[0].split(" ")[0] ?
                                                value.cn[0].split(" ")[0]
                                              :
                                                false)
                                            : 
                                              false)
                              ),
                  //If last name given use that, else if fill name given split and use second half, if exists else false
                  last_name: ((value.sn && value.sn[0]) ?
                                            value.sn[0]
                                          :
                                            ((value.cn && value.cn[0]) ?
                                              (value.cn[0].split(" ")[1] ?
                                                value.cn[0].split(" ")[1]
                                              :
                                                false)
                                            : 
                                              false)
                              ),
                  student_email: ((value.mail && value.mail[0]) ? value.mail[0] : false),
                  student_id: ((value.umanpersonid && value.umanpersonid[0]) ? value.umanpersonid[0] : false),
                  student_barcode: ((value.umanmagstripe && value.umanmagstripe[0]) ? value.umanmagstripe[0] : false),
                  student_year: ((value.umanstudentyearofstudy && value.umanstudentyearofstudy[0]) ? value.umanstudentyearofstudy[0] : false),
                  course_faculty: ((value.umanprimaryou && value.umanprimaryou[0]) ? value.umanprimaryou[0] : false),
                  student_title: ((value.title && value.title[0]) ? value.title[0] : false),
                }                
                
                //console.log(person);
                
                var this_li = $("<li>" + highlight_term(person.full_name, query) + " - " + person.student_title + " - " + person.course_faculty + "</li>")
                                  .appendTo(dropdown_ul);
                                  
                $.data(this_li.get(0), "data", person);
                
            });

            show_dropdown();

            current_dropdown_ul = dropdown_ul.get(0);
            dropdown_ul.slideDown("fast");
        } else {
            show_dropdown_noresults();
        }
      }
      
      // Highlight an item in the results dropdown
      function select_dropdown_item (item) {
          if(item) {
              if(selected_dropdown_item) {
                  deselect_dropdown_item($(selected_dropdown_item));
              }
  
              item.addClass("selected");
              selected_dropdown_item = item.get(0);
          }
      }

      // Remove highlighting from an item in the results dropdown
      function deselect_dropdown_item (item) {
          item.removeClass("selected");
          selected_dropdown_item = null;
      }
      
      function hide_dropdown() {
        dropdown.hide().empty();
      }
      
      function show_dropdown() {
          dropdown
              .css({
                  position: "absolute",
                  width: input.outerWidth() - 8,
                  top: input.offset().top + input.outerHeight() - 2,
                  left: input.offset().left + 3,
                  zindex: 999
              })
              .show();
      }
      
      function show_dropdown_tooshort() {
        dropdown.html("<p>Please enter more than three letters</p>");
        show_dropdown();
      }
  
      function show_dropdown_searching () {
        dropdown.html("<p>Searching...</p>");
        show_dropdown();
      }
      
      function show_dropdown_failed () {
        dropdown.html("<p>Search failed, please try later</p>");
        show_dropdown();
      }
  
      function show_dropdown_hint () {
        dropdown.html("<p>Type your name or student id</p>");
        show_dropdown();
      } 
      
      function show_dropdown_noresults() {
          dropdown.html("<p>No results, try again or <a id='skip' href='#'>enter details manually</a></p>");
          dropdown.mousedown(function (event) {
            second_page();
          });
          show_dropdown();      
      }
      
      // Highlight the query part of the search term
      function highlight_term(value, term) {
          return value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<b>$1</b>");
      }  
      
      function is_printable_character(keycode) {
        return ((keycode >= 48 && keycode <= 90) ||     // 0-1a-z
                (keycode >= 96 && keycode <= 111) ||    // numpad 0-9 + - / * .
                (keycode >= 186 && keycode <= 192) ||   // ; = , - . / ^
                (keycode >= 219 && keycode <= 222));    // ( \ ) '
      }
      
      function second_page(selected) {
        //Reset these
        selected_dropdown_item = null;
        current_dropdown_ul = null;
        if(selected) {
          var person = $.data(selected.get(0), "data");
          input.val(selected.text());
          
          for (var key in person) {
            var element = $("#"+key);
            element.val(person[key]);
          }
        }
        input.blur();
        hide_dropdown();
        
        
        $("#page2").slideDown(500);
        
        console.log(person);
      }
      
      function second_page_error(message) {
        if($(":animated").length != 0) {
          setTimeout(function(){second_page_error(message);}, 1000);
          return false;
        } 
        $("#loader").stop(true, false);
        $("#loader").fadeOut(500, function(){
          $("#page2").slideDown(500, function() {
            $("#imessageERROR").html(message).slideDown(250);
          });
        });
      }
      
      function gotoPage() {
        $("#page2").slideDown(500);
      }
      
      
      $("#page2form").submit(function() {
        $(".required").removeClass("required");
        $("#imessageERROR").slideUp(100);
        submit = true;
        if($("#first_name").val() == "") {
            $("#first_name").addClass("required");
            submit = false;
        }
        if($("#last_name").val() == "") {
            $("#last_name").addClass("required");
            submit = false;
        }
        if($("#student_id").val() == "" || isNaN($("#student_id").val())) {
            $("#student_id").addClass("required");
            submit = false;
        }
        if($("#student_email").val() == "" || !isValidEmailAddress($("#student_email").val())) {
            $("#student_email").addClass("required");
            submit = false;
        } 
        if($("#initials").val() == "") {
            $("#initials").addClass("required");
            submit = false;
        }      
        
        if(!submit) {
          $("#imessageERROR").html("You missed, or incorrectly filled in some fields, please try again!").slideDown(250);
        } else {
          submit = false;
          $("#page2").slideUp(500, function() {
            $("#loader").fadeIn(500);
                 
            var data = {};
            $("#page2 input").each(function() {
               data[this.id] = this.value;
            });
  
            console.log(data);
            $.post("addUser.php", data,
              function(data){
                if(data==6) second_page_error("You already have an account! Thanks for trying again though.");
                else if(data!=1) second_page_error("There was a problem saving your data, it was rejected, please try again." + (!isNaN(parseFloat(data)) ? " Error code: "+data : ""));
                else {
                  $("#imessageERROR").hide();
                  $("#loader").fadeOut(500, function(){
                    $("#page3").slideDown(500);
                    resetPage();
                  }); 
                }
              }
            ).error(function() { second_page_error("I couldn't save your details, is the internet down?"); });
          });          
        }
           
        return submit;
        
      });
      
      
      
      function isValidEmailAddress(emailAddress) {
        var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
        return pattern.test(emailAddress);
      }
      
      function addAudio(options, source) {
        var audio = $('<audio>', options);
        $('<source>').attr({'src': source+".ogg", 'type': 'audio/ogg'}).appendTo(audio);
        $('<source>').attr({'src': source+".mp3", 'type': 'audio/mpeg'}).appendTo(audio);
        
        audio.appendTo('body');
        return audio.get(0);
      }
  
      // Create a new reader instance
      var reader = new CardReader();
      var fail = addAudio({"preload": 'auto'}, "audio/fail");
      var read = addAudio({"preload": 'auto'}, "audio/read");
      
      // Feed it an object to observe (this could also be a textbox)
      reader.observe(window);
      
      // Errback in case of a reading error
      reader.cardError(function () {
          fail.play();
          alert("A read error occurred, are you sure that's a student card?");
      });
      
      // Callback in case of a successful reading operation
      reader.cardRead(function (value) {
          read.play();
          $("#mainInput").val(value);
          search();
      });
      
      // Add a new validation hook to the reader
      reader.validate(function (value) {
          // Tests if the value is a number (cleverer regex needed?)
          var pattern = new RegExp(/^\d*$/);
      
          return pattern.test(value);
      });
      
      /* Countdown and form reset */
      $.fn.clearForm = function() {
        return this.each(function() {
          var type = this.type, tag = this.tagName.toLowerCase();
            if (tag == 'form')
              return $(':input',this).clearForm();
            if (type == 'text' || type == 'password' || tag == 'textarea')
              this.value = '';
            else if (type == 'checkbox' || type == 'radio')
              this.checked = false;
          else if (tag == 'select')
              this.selectedIndex = -1;
        });
      };
      
      var countdown;
      function resetPage() {
         var seconds = 30;
         countdown = setTimeout(updateCountdown, 1000);
         var element = $("#countdown");
      
         function updateCountdown() {
            seconds--;
            if (seconds > 0) {
               element.text(seconds);
               countdown = setTimeout(updateCountdown, 1000);
            } else {
               $("#page2").slideUp(500);
               $("#page3").slideUp(500);
               $("#imessageERROR").slideUp(500);
               $(':input').clearForm();
               $("#loader").fadeOut(500);
               element.text(30);
            }
         }
      };
       
    });   
          
    </script>
</head>

<body>
    <div class="wrap">
        <h1>Gaming Society Signup</h1>
        <input type="text" autocomplete="off" placeholder="Type your name or ID to begin" id="mainInput" name="mainInput" />
        <div class="dropdown" id="dropdown"></div>
    </div> 
    <div class="wrap" id="page2" style="display:none">
        <form class="iform" action="index.php" id="page2form" method="POST">
          <ul>
            <!--<li class="iheader" id="aboutYou">About You</li>-->
            <li id="imessageERROR"></li>
            <li><label for="first_name">*Name</label><input class="itext" type="text" name="first_name" id="first_name" placeholder="First" />
                                                     <input class="itext" type="text" name="last_name" id="last_name" placeholder="Last" />
                </li>
            <li><label for="student_id">*Student ID</label><input class="itext" type="text" name="student_id" id="student_id" placeholder="On your ID card" /></li>
            <li><label for="student_email">*Email</label><input class="itext" type="text" name="student_email"
                                                                id="student_email" placeholder="Personal or Student" style="width: 320px;" /></li>
            <li><label for="tac">*Initials</label><small>I have would like to become a member of this society and I am a student at the University of Manchester &nbsp</small><input class="itext" type="text" name="initials"
                                                                id="initials" placeholder="Initials" style="width: 50px;" /></li>
            <li><label>&nbsp;</label><input type="submit" class="ibutton" name="submit" id="submit" value="Join the Society" /></li>                                                    
          </ul>
          
        </form>
    </div>
    <div id="loader"><img src="images/ajax-loader.gif" /></div>
    <div class="wrap" id="page3" style="display:none">
      <div class="iform center">
        <ul>
          <li>Awesome, you've signed up!</li>
          <li>Thanks for your time, look out for an email from us soon!</li>
          <li>If you have any questions feel free to ask one of our team for more information.</li>
          <li><small>This form will reset automatically in <span id="countdown">30</span> seconds</small></li>
        </ul>
      </div>
    </div>  
    <div id="noscript">
      <div class="message">
        You must have Javascript enabled for this, and most mainstream websites to work. Please enable it or download a more modern browser, such as Google Chrome.
      </div>
      <div class="overlay"></div>
    </div>
    <div class="hidden">
      <img src="images/ajax-loader.gif" />
    </div>
</body>
</html>