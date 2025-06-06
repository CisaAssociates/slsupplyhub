$(document).ready(function () {
  $(".image-popup").magnificPopup({
    type: "image",
    closeOnContentClick: !1,
    closeBtnInside: !1,
    mainClass: "mfp-with-zoom mfp-img-mobile",
    image: {
      verticalFit: !0,
      titleSrc: function (e) {
        return e.el.attr("title");
      },
    },
    gallery: { enabled: !0 },
    zoom: {
      enabled: !0,
      duration: 300,
      opener: function (e) {
        return e.find("img");
      },
    },
  }),
    $(".filter-menu .filter-menu-item").click(function () {
      $(".filter-menu .filter-menu-item").removeClass("active"),
        $(this).addClass("active");
    }),
    $(function () {
      var e;
      $(".filter-menu-item").click(function () {
        (e = $(this).attr("data-rel")),
          $(".filterable-content").fadeTo(100, 0),
          $(".filterable-content .filter-item")
            .not("." + e)
            .fadeOut()
            .removeClass(""),
          setTimeout(function () {
            $("." + e)
              .fadeIn()
              .addClass(""),
              $(".filterable-content").fadeTo(300, 1);
          }, 300);
      });
    });
});
