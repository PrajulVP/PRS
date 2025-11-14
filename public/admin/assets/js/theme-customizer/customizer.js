(function ($) {
  if (localStorage.getItem("color"))
    $("#color").attr(
      "href",
      "../assets/css/" + localStorage.getItem("color") + ".css"
    );
  if (localStorage.getItem("dark")) $("body").addClass("dark-only");
  $(
    '<div class="customizer-links"><div class="nav flex-column nac-pills" id="c-pills-tab" role="tablist" aria-orientation="vertical"><a class="nav-link" id="c-pills-home-tab" data-bs-toggle="pill" href="#c-pills-home" role="tab" aria-controls="c-pills-home" aria-selected="true" data-original-title=""><div class="settings"><img class="img-fluid" src="../assets/images/customizer/1.png" alt="nft"></div><span>Quick option</span></a> <a class="nav-link" target="_blank" href="https://docs.pixelstrap.net/admin/riho/document/" data-original-title=""><div><img class="img-fluid fa-spin" src="../assets/images/customizer/2.png" alt="nft"></div><span>Document</span></a> <a class="nav-link" target="_blank" href="https://themeforest.net/user/pixelstrap/portfolio" target="_blank" data-original-title=""><div><img class="img-fluid" src="../assets/images/customizer/3.png" alt="nft"></div><span>Buy Now</span></a> <a class="nav-link" target="_blank" href="https://admin.pixelstrap.net/riho/template/landing-page.html" data-original-title=""><div><img class="img-fluid" src="../assets/images/customizer/4.png" alt="nft"></div><span>Check features</span></a> <a class="nav-link" target="_blank" href="https://support.pixelstrap.com/portal/en/signin" data-original-title=""><div><img class="img-fluid" src="../assets/images/customizer/5.png" alt="nft"></div><span>Support</span></a></div></div><div class="customizer-contain"><div class="tab-content" id="c-pills-tabContent"><div class="customizer-header"><i class="icofont-close icon-close"></i><span class="f-20 f-w-600 txt-dark">Unlocking Creativity</span><p class="mb-0">Try It Real Time <i class="fa fa-thumbs-o-up txt-primary"></i></p></div><div class="customizer-body custom-scrollbar"><div class="tab-pane fade show active" id="c-pills-home" role="tabpanel" aria-labelledby="c-pills-home-tab"><h5>Layout Type</h5><ul class="main-layout d-flex align-items-center layout-grid"><li data-attr="ltr" class="active"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><span class="badge badge-primary">LTR</span></div></li><li data-attr="rtl"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><span class="badge badge-primary">RTL</span></div></li><li data-attr="box-layout"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><span class="badge badge-primary">Box</span></div></li></ul><hr><h5>Sidebar Type</h5><ul class="sidebar-type layout-grid"><li data-attr="compact-sidebar"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><span class="badge badge-primary">Compact</span></div></li><li data-attr="normal-sidebar"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><span class="badge badge-primary">Normal</span></div></li></ul><hr><h5>Sidebar Icon</h5><ul class="sidebar-type layout-grid"><li data-attr="material-icon"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><span class="badge badge-primary">Material</span></div></li><li data-attr="stroke-icon"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><span class="badge badge-primary">Stroke</span></div></li></ul><hr><h5>Layout settings</h5><ul class="layout-grid customizer-mix"><li class="color-layout" data-attr="light-only"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><h6>Light Layout</h6></div></li><li class="color-layout" data-attr="dark-sidebar"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><h6>Dark Sidebar</h6></div></li><li class="color-layout" data-attr="dark-only"><div class="header bg-light"><ul><li></li><li></li><li></li></ul></div><div class="body"><h6>Dark Layout</h6></div></li></ul><hr><h5>Color Option</h5><ul class="customizer-color d-flex g-2"><li><a class="f-w-700" href="javascript:void(0)" data-attr="color-1">Default</a></li><li><a class="f-w-700" href="javascript:void(0)" data-attr="color-2">Color 2</a></li><li><a class="f-w-700" href="javascript:void(0)" data-attr="color-3">Color 3</a></li><li><a class="f-w-700" href="javascript:void(0)" data-attr="color-4">Color 4</a></li><li><a class="f-w-700" href="javascript:void(0)" data-attr="color-5">Color 5</a></li><li><a class="f-w-700" href="javascript:void(0)" data-attr="color-6">Color 6</a></li></ul><hr><h5>Unlimited Color</h5><div class="color-picker-block"><div class="form-group"><label class="form-label" for="ColorPicker1">Primary Color</label><input class="form-control" id="ColorPicker1" type="color" value="#5c61f2"></div><div class="form-group"><label class="form-label" for="ColorPicker2">Secondary Color</label><input class="form-control" id="ColorPicker2" type="color" value="#eeb82f"></div></div><div class="color-apply-btn"><button class="btn btn-primary" onclick="colorApply()">Apply</button><button class="btn btn-primary" onclick="colorReset()">Reset</button></div></div></div></div></div>'
  ).appendTo($("body"));
  (function () {})();
  //live customizer js
  $(document).ready(function () {
    $(".customizer-color li").on("click", function () {
      $(".customizer-color li").removeClass("active");
      $(this).addClass("active");
      var color = $(this).attr("data-attr");
      var primary = $(this).attr("data-primary");
      var secondary = $(this).attr("data-secondary");
      localStorage.setItem("color", color);
      localStorage.setItem("primary", primary);
      localStorage.setItem("secondary", secondary);
      localStorage.removeItem("dark");
      $("#color").attr("href", "../assets/css/" + color + ".css");
      $("body").removeClass("dark-only");
     // location.reload(true);
    });

    $(".customizer-color.dark li").on("click", function () {
      $(".customizer-color.dark li").removeClass("active");
      $(this).addClass("active");
      var color = $(this).attr("data-attr");
      $("body").addClass("dark-only");
       $("#color").attr("href", "../assets/css/" + color + ".css");
      localStorage.setItem("dark", "dark-only");
      localStorage.setItem("color", color);
    });

    $(".customizer-mix li").on("click", function () {
      $(".customizer-mix li").removeClass("active");
      $(this).addClass("active");
      var mixLayout = $(this).attr("data-attr");
      if (mixLayout == "light-only") {
        $("body").removeClass("dark-only");
        localStorage.removeItem("dark");
      } else {
        $("body").addClass("dark-only");
        localStorage.setItem("dark", "dark-only");
      }
    });

    if (localStorage.getItem("primary") != null) {
      document.documentElement.style.setProperty(
        "--theme-deafult",
        localStorage.getItem("primary")
      );
    }
    if (localStorage.getItem("secondary") != null) {
      document.documentElement.style.setProperty(
        "--theme-secondary",
        localStorage.getItem("secondary")
      );
    }
    $(
      ".customizer-links #c-pills-home-tab, .customizer-links #c-pills-layouts-tab"
    ).click(function () {
      $(".customizer-contain").addClass("open");
      $(".customizer-links").addClass("open");
    });

    $(".close-customizer-btn").on("click", function () {
      $(".floated-customizer-panel").removeClass("active");
    });

    $(".customizer-contain .icon-close").on("click", function () {
      $(".customizer-contain").removeClass("open");
      $(".customizer-links").removeClass("open");
    });

    $(".color-apply-btn").click(function () {
      location.reload(true);
    });

    var primary = document.getElementById("ColorPicker1").value;
    document.getElementById("ColorPicker1").onchange = function () {
      primary = this.value;
      localStorage.setItem("primary", primary);
      document.documentElement.style.setProperty("--theme-primary", primary);
    };

    var secondary = document.getElementById("ColorPicker2").value;
    document.getElementById("ColorPicker2").onchange = function () {
      secondary = this.value;
      localStorage.setItem("secondary", secondary);
      document.documentElement.style.setProperty(
        "--theme-secondary",
        secondary
      );
    };

    $(".sidebar-setting li").on("click", function () {
      $(".sidebar-setting li").removeClass("active");
      $(this).addClass("active");
      var sidebar = $(this).attr("data-attr");
      $(".sidebar-wrapper").attr("data-layout", sidebar);
    });

    $(".sidebar-main-bg-setting li").on("click", function () {
      $(".sidebar-main-bg-setting li").removeClass("active");
      $(this).addClass("active");
      var bg = $(this).attr("data-attr");
      $(".sidebar-wrapper").attr("class", "sidebar-wrapper " + bg);
    });

    $(".sidebar-type li").on("click", function () {
      $("body").append("");
      console.log("test");
      var type = $(this).attr("data-attr");

      var boxed = "";
      if ($(".page-wrapper").hasClass("box-layout")) {
        boxed = "box-layout";
      }
      switch (type) {
        case "compact-sidebar": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-wrapper " + boxed
          );
          $(this).addClass("active");
          localStorage.setItem("page-wrapper", "compact-wrapper");
          break;
        }
        case "normal-sidebar": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper horizontal-wrapper " + boxed
          );
          $(".logo-wrapper")
            .find("img")
            .attr("src", "../assets/images/logo/logo.png");
          localStorage.setItem("page-wrapper", "horizontal-wrapper");
          break;
        }
        case "default-body": {
          $(".page-wrapper").attr("class", "page-wrapper  only-body" + boxed);
          localStorage.setItem("page-wrapper", "only-body");
          break;
        }
        case "dark-sidebar": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-wrapper dark-sidebar" + boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "compact-wrapper dark-sidebar"
          );
          break;
        }
        case "compact-wrap": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-sidebar" + boxed
          );
          localStorage.setItem("page-wrapper", "compact-sidebar");
          break;
        }
        case "color-sidebar": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-wrapper color-sidebar" + boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "compact-wrapper color-sidebar"
          );
          break;
        }
        case "compact-small": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-sidebar compact-small" + boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "compact-sidebar compact-small"
          );
          break;
        }
        case "box-layout": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-wrapper box-layout " + boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "compact-wrapper box-layout"
          );
          break;
        }
        case "enterprice-type": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper horizontal-wrapper enterprice-type" + boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "horizontal-wrapper enterprice-type"
          );
          break;
        }
        case "modern-layout": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-wrapper modern-type" + boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "compact-wrapper modern-type"
          );
          break;
        }
        case "material-layout": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper horizontal-wrapper material-type" + boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "horizontal-wrapper material-type"
          );

          break;
        }
        case "material-icon": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-sidebar compact-small material-icon" + boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "compact-sidebar compact-small material-icon"
          );

          break;
        }
        case "advance-type": {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper horizontal-wrapper enterprice-type advance-layout" +
              boxed
          );
          localStorage.setItem(
            "page-wrapper",
            "horizontal-wrapper enterprice-type advance-layout"
          );

          break;
        }
        default: {
          $(".page-wrapper").attr(
            "class",
            "page-wrapper compact-wrapper " + boxed
          );
          localStorage.setItem("page-wrapper", "compact-wrapper");
          break;
        }
      }
      // $(this).addClass("active");
      location.reload(true);
    });

    $(".main-layout li").on("click", function () {
      $(".main-layout li").removeClass("active");
      $(this).addClass("active");
      var layout = $(this).attr("data-attr");
      $("body").attr("class", layout);
      $("html").attr("dir", layout);
    });

    $(".main-layout .box-layout").on("click", function () {
      $(".main-layout .box-layout").removeClass("active");
      $(this).addClass("active");
      var layout = $(this).attr("data-attr");
      $("body").attr("class", "box-layout");
      $("html").attr("dir", layout);
    });
  });
})(jQuery);