"use strict";

$(document).ready(function () {
  //=============================================================================================================
  //#region GENERAL VARIABLE

  let $body = $("body");
  let $clickCounter = 0;

  //#endregion
  //=============================================================================================================

  //=============================================================================================================
  //#region APPLICATION FUNCTION

  //ajax for post.php
  function xpost($button, $param, $action) {
    $.ajax({
      type: "POST",
      url: "./post.php",
      data: { param: $param },
      beforeSend: function () {
        if ($button != "") {
          $button.prop("disabled", true).children().not(".img_spinner").setOff();
          $button.find(".img_spinner").setOn();
        }
      },
      success: $action
    }).done(function () {
      if ($button != "") {
        $button.prop("disabled", false).children().not(".img_spinner").setOn();
        $button.find(".img_spinner").setOff();
      }
    });
  }

  //#endregion
  //=============================================================================================================

  //=============================================================================================================
  //#region COMMON ELEMENT FUNCTION

  //show element
  $.fn.setOn = function () {
    this.removeClass("off").addClass("on");
    return this; //so this function can be chained for other function
  };

  //hide element
  $.fn.setOff = function () {
    this.removeClass("on").addClass("off");
    return this; //so this function can be chained for other function
  };

  //check if element is visible
  $.fn.isOn = function () {
    return this.hasClass("on");
  };

  //check if element is hidden
  $.fn.isOff = function () {
    return this.hasClass("off");
  };

  //set vertical scroll position to the desired location
  function setScrollTo($targetElement, $definedPosition, $duration) {
    if ($targetElement == "") {
      if ($definedPosition == "top") $("html, body, #div_contentContainer").animate({ scrollTop: 0 }, $duration);
      else if ($definedPosition == "bottom") $("html, body, #div_contentContainer").animate({ scrollTop: $(document).height() }, $duration);
      else $("html, body, #div_contentContainer").animate({ scrollTop: $definedPosition }, $duration);
    }
    else {
      $targetElement.addClass("scrollFocused");
      if ($(".scrollFocused").length) { //set the vertical scroll position if .scrollFocused is not empty
        //NOT PERFECT YET BECAUSE SCREWED BY MOBILE BROWSER SOFT KEYBOARD
        $("html, body, #div_contentContainer").animate({ scrollTop: $(".scrollFocused").offset().top - document.documentElement.clientHeight / 2 }, $duration);
      }
      $(".scrollFocused").removeClass("scrollFocused");
    }
  }

  //make setScrollTo function chainable
  $.fn.goTo = function ($duration) {
    setScrollTo(this, "", $duration);
    this.focus();
    return this; //so this function can be chained for other function
  };

  //check if all input is filled completely
  function checkInput($inputArray) {
    let $result = 0;

    for (let $i = 0; $i < $inputArray.length; $i++) {
      $inputArray[$i].notify("clear", "");

      let $value = $inputArray[$i].val();
      let $floatValue = parseFloat($value);
      let $min = parseFloat($inputArray[$i].attr("min"));
      let $max = parseFloat($inputArray[$i].attr("max"));
      let $step = $inputArray[$i].attr("step");
      let $boundaryErrorText = "";
      let $emptyErrorText = "Cannot Be Empty";

      $inputArray[$i].blur(); //check any unique requirement per input field

      if (!isNaN($min)) { //generate the error text for boundary error
        $boundaryErrorText = "minimum value " + $min;
        if (!isNaN($max)) $boundaryErrorText = "range start from " + $min + " to " + $max;
      }
      else if (!isNaN($max)) $boundaryErrorText += "maximum value " + $max;

      if (typeof $step != "undefined") $boundaryErrorText += !isNaN($min) || !isNaN($max) ? " with interval " + $step : "interval = " + $step;

      if ($inputArray[$i].closest(".div_field").hasClass("mandatory")) { //check if mandatory input value is empty
        if ($inputArray[$i].is("select")) { //check if select value is 0
          if ($inputArray[$i].find("option:selected").val() == 0) $inputArray[$i].notify("error", $emptyErrorText);
        }
        else { //check if value is empty or still not filed at all
          if (!$.trim($value)) $inputArray[$i].notify("error", $emptyErrorText);
        }
      }

      //check if number input value out of boundary
      if ($floatValue < $min || $floatValue > $max) $inputArray[$i].notify("error", $boundaryErrorText);

      //check if number input value interval is not correct
      if (typeof $step != "undefined" && $floatValue % $step != 0) $inputArray[$i].notify("error", $boundaryErrorText);

      if ($inputArray[$i].hasClass("error")) {
        $result += 1;
        if ($result == 1) $inputArray[$i].goTo(100); //set cursor and scroll position to this first of error input
      }
    }

    return $result;
  }

  //show or hide any notification
  $.fn.setNotif = function ($type, $content) {
    if ($type == "") this.removeClass("normal warning error").fadeOut("fast");
    this.addClass($type).fadeIn("fast").html($content || "&nbsp");

    return this; //so this function can be chained for other function
  };

  //show or hide notification for each input
  $.fn.notify = function ($type, $message) {
    if ($type == "clear") this.removeClass("error").closest(".div_field").find(".div_notif").setNotif("", "");
    else {
      let $inputClass = $type == "error" ? "error" : "";
      this.addClass($inputClass).closest(".div_field").find(".div_notif").setNotif($type, $message);
    }

    return this; //so this function can be chained for other function
  };

  //check if input value already exist and can not be same
  $.fn.checkExist = function () {
    let $unique = this.attr("unique");
    let $initial = this.attr("initial");
    let $input = this;
    let $value = this.val();

    if (typeof $unique != "undefined") {
      if (typeof $initial != "undefined" && $value.toLowerCase() != $initial.toLowerCase()) {
        let $uniquePart = $unique.split("_");
        let $param = JSON.stringify({ type: "checkExist", table: $uniquePart[0], column: $uniquePart[1], value: $value });

        xpost("", $param, function ($result) {
          $result == "exist" ? $input.notify("error", $uniquePart[1] + " already registered") : $input.notify("clear", "");
        });
      }
      else if (typeof $initial != "undefined" && $value.toLowerCase() == $initial.toLowerCase()) this.notify("clear", "");
    }

    return this; //so this function can be chained for other function
  };

  //check if name not contain space or underscore
  $.fn.checkName = function () {
    let $value = this.val();

    if (/^[^_\s]*$/.test($value)) { //check if no space and underscore
      this.notify("clear", "");
      this.checkExist();
    }
    else this.notify("error", "do not use space or underscore");

    return this; //so this function can be chained for other function
  };

  //check if ip address format is correct
  $.fn.checkIpNumber = function () {
    let $value = this.val();
    let $ipFormat = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;

    if ($value == "") this.notify("clear", "");
    else {
      if ($value.match($ipFormat)) {
        this.notify("clear", "");
        this.checkExist();
      }
      else this.notify("error", "invalid IP address");
    }

    return this; //so this function can be chained for other function
  };

  //#endregion
  //=============================================================================================================

  //=============================================================================================================
  //#region COMMON

  //show or hide bt_toTop when scroll the content
  $(window).scroll(function () {
    let $bt_toTop = $("#bt_toTop");
    $(this).scrollTop() > 100 ? $bt_toTop.fadeIn() : $bt_toTop.fadeOut();
  });

  //do something wherever body clicked
  $body.on("click", function () {
    window.location.hash = String(++$clickCounter); //prevent back button for this single page application
  });

  //stop submitting form if enter pressed on input
  $body.on("keydown", "input", function ($e) {
    if ($e.keyCode == "13") $e.preventDefault();
  });

  //#endregion
  //=============================================================================================================

  //=============================================================================================================
  //#region MONITOR

  //show form to add
  $body.on("click", "#a_add", function () {
    window.open("./php/form.php", "_blank");
  });

  //show form to update or remove
  $body.on("click", ".div_device", function () {
    window.open("./php/form.php" + "?name=" + $(this).find(".sp_name").html(), "_blank");
  });

  //scroll back top top when btn_toTop clicked
  $body.on("click", "#bt_toTop", function () {
    setScrollTo("", "top", 100);
  });

  //#endregion
  //=============================================================================================================

  //=============================================================================================================
  //#region FORM

  //check if name already exist
  $body.on("blur", "#inTex_name", function () {
    $(this).checkName();
  });

  //check if ip format is correct
  $body.on("blur", "#inTex_number", function () {
    $(this).checkIpNumber();
  });

  //register or update new device
  $body.on("click", "#bt_save", function () {
    let $type = $("h1").text() == "ADD NEW DEVICE" ? "insert" : "update";
    let $status = "1";
    let $lastupdate = "0000-00-00 00:00:00";
    let $inputArray = [];

    if ($type == "update") {
      $status = $("#inTex_status").val() == "online" ? "1" : "0";
      $lastupdate = $("#inTex_lastUpdate").val();
    }

    $("form").find("input, textarea, select").each(function () { $inputArray.push($(this)); });

    if (checkInput($inputArray) == 0) {
      let $param = JSON.stringify({
        type: $type,
        id: $("#div_id").text(),
        data: [
          {
            column: "ip.name",
            value: $("#inTex_name").val()
          },
          {
            column: "ip.number",
            value: $("#inTex_number").val()
          },
          {
            column: "ip.priority",
            value: $("#inNum_priority").val()
          },
          {
            column: "ip.status",
            value: $status
          },
          {
            column: "ip.lastupdate",
            value: $lastupdate
          },
          {
            column: "ip.remarks",
            value: $("#ta_remarks").val()
          }
        ]
      });

      xpost($(this), $param, function ($result) {
        if ($result == "") window.close();
      });
    }
  });

  //delete device
  $body.on("click", "#bt_delete", function () {
    if (confirm("Really want to delete " + $("h1").text() + "?")) {
      let $param = JSON.stringify({ type: "delete", id: $("#div_id").text() });

      xpost($(this), $param, function ($result) {
        if ($result == "") window.close();
      });
    }
  });

  //#endregion
  //=============================================================================================================
});