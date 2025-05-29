jQuery(document).ready(function ($) {
  $(".f_heading").on("click", function () {
    // Remove active class from all headings

    $(".f_heading").removeClass("active");

    // Hide all accordion content

    $(".f_content").hide();

    // Add active class to the clicked heading

    $(this).addClass("active");

    // Show the corresponding content

    $(this).next(".f_content").show();
  });
});

// Mandeep code

document.addEventListener("DOMContentLoaded", function () {
  document.addEventListener("click", function (event) {
    const filterSpan = event.target.closest(".woof-handle");

    const slideOutDiv = document.querySelector(".woof-slide-out-div");

    if (filterSpan && slideOutDiv) {
      if (slideOutDiv.classList.contains("ui-slideouttab-open")) {
        filterSpan.textContent = "×"; // Change text to ×
      } else {
        filterSpan.textContent = "Filter"; // Revert back when closed
      }
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    let parentarrow = document.getElementById("splide01");

    // console.log(parentarrow);

    if (parentarrow) {
      let arrowButton = parentarrow.querySelector(".splide__arrow--prev");

      // Remove the existing SVG

      arrowButton.innerHTML = "";

      // Create a new SVG element and insert it

      let newSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="76.039" height="147.837" viewBox="0 0 76.039 147.837"><path id="Path_220" data-name="Path 220" d="M6267.379,465.491l71.8,71.8-71.8,71.8" transform="translate(-6265.258 -463.369)" fill="none" stroke="#bc9b6a" stroke-linecap="round" stroke-width="3"/></svg>`;

      arrowButton.insertAdjacentHTML("beforeend", newSVG);
    }
  }, 500); // Delay to allow dynamic elements to load
});

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    let parentarrow = document.getElementById("splide01");

    // console.log(parentarrow);

    if (parentarrow) {
      let arrowButton = parentarrow.querySelector(".splide__arrow--next");

      // Remove the existing SVG

      arrowButton.innerHTML = "";

      // Create a new SVG element and insert it

      let newSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="76.039" height="147.837" viewBox="0 0 76.039 147.837"><path id="Path_220" data-name="Path 220" d="M6267.379,465.491l71.8,71.8-71.8,71.8" transform="translate(-6265.258 -463.369)" fill="none" stroke="#bc9b6a" stroke-linecap="round" stroke-width="3"/></svg>`;

      arrowButton.insertAdjacentHTML("beforeend", newSVG);
    }
  }, 500); // Delay to allow dynamic elements to load
});

jQuery(function ($) {
  // Cache product title and store original title
  let productTitle = $(".product_title");
  if (!productTitle.data("original-title")) {
    productTitle.data("original-title", productTitle.text().trim());
  }

  // Function to get selected color and size, then update title
  function updateProductTitle() {
    let selectedColor =
      $(".image-variable-item.selected").attr("data-wvstooltip") || "";
    let selectedSize = $('select[name="attribute_pa_size"]').val() || "";

    // Build new title
    let newTitle = productTitle.data("original-title");
    if (selectedColor) newTitle += " - " + selectedColor;
    if (selectedSize) newTitle += " / " + selectedSize;

    // Update product title immediately
    productTitle.text(newTitle);
  }

  // Function to update description when variation is found
  function updateDescription(variation) {
    if (variation && variation.variation_description) {
      $(".woocommerce-product-details__short-description").html(
        variation.variation_description
      );
    }
  }

  // Trigger updates when color is clicked
  $('ul[data-attribute_name="attribute_pa_colour"]').on(
    "click",
    ".image-variable-item",
    function () {
      $(".image-variable-item").removeClass("selected");
      $(this).addClass("selected");
      updateProductTitle();
    //   $("form.variations_form").trigger("check_variations"); // Force WooCommerce to find variation
    }
  );

  // Trigger updates when size is changed
  $("form.variations_form").on(
    "change",
    'select[name="attribute_pa_size"]',
    function () {
      updateProductTitle();
      $("form.variations_form").trigger("check_variations"); // Force WooCommerce to find variation
    }
  );

  // Update description when variation is found (WooCommerce default event)
  $("form.variations_form").on("found_variation", function (event, variation) {
    updateDescription(variation);
  });

  // Initial update in case of pre-selected options
  $(document).ready(function () {
    updateProductTitle();
    $("form.variations_form").trigger("check_variations");
  });
});
