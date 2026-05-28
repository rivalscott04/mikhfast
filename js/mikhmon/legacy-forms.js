/* Mikhmon — Hotspot form helpers (RequiredV, defUserl) */
function RequiredV() {
  var mode = document.getElementById("expmode").value;
  var validityStyle = document.getElementById("validity").style;
  var validi = document.getElementById("validi");

  if (mode === "rem" || mode === "remc" || mode === "ntf" || mode === "ntfc") {
    validityStyle.display = "table-row";
    validi.type = "text";
    if (validi.value === "") validi.value = "";
    $("#validi").focus();
  } else {
    validityStyle.display = "none";
    validi.type = "hidden";
  }
}

function defUserl() {
  var userType = document.getElementById("user").value;

  var numStyle = document.getElementById("num").style;
  var lowerStyle = document.getElementById("lower").style;
  var upperStyle = document.getElementById("upper").style;
  var upplowStyle = document.getElementById("upplow").style;

  var lower1Style = document.getElementById("lower1").style;
  var upper1Style = document.getElementById("upper1").style;
  var upplow1Style = document.getElementById("upplow1").style;

  var mixStyle = document.getElementById("mix").style;
  var mix1Style = document.getElementById("mix1").style;
  var mix2Style = document.getElementById("mix2").style;

  if (userType === "up") {
    $("select[name=userl] option:first").html("4");
    $("select[name=char] option:first").html("Random abcd");

    numStyle.display = "none";
    lowerStyle.display = "block";
    upperStyle.display = "block";
    upplowStyle.display = "block";

    lower1Style.display = "none";
    upper1Style.display = "none";
    upplow1Style.display = "none";

    mixStyle.display = "block";
    mix1Style.display = "block";
    mix2Style.display = "block";
  } else if (userType === "vc") {
    $("select[name=userl] option:first").html("8");
    $("select[name=char] option:first").html("Random abcd2345");

    numStyle.display = "block";
    lowerStyle.display = "none";
    upperStyle.display = "none";
    upplowStyle.display = "none";

    lower1Style.display = "block";
    upper1Style.display = "block";
    upplow1Style.display = "block";

    mixStyle.display = "block";
    mix1Style.display = "block";
    mix2Style.display = "block";
  }
}
