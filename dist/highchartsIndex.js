/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/highcharts/ajax.js":
/*!********************************!*\
  !*** ./src/highcharts/ajax.js ***!
  \********************************/
/***/ (() => {

jQuery(function ($) {
  // the Configure link click event
  $('[id^="rrze_statistik_widget"] .edit-box.open-box').click(function () {
    var button = $(this);
    var selector = $(this).parent().parent().parent().parent().attr('id');
    console.log(selector);
    $.ajax({
      url: ajaxurl,
      // it is predefined in /wp-admin/
      type: 'POST',
      data: 'action=showform',
      beforeSend: function beforeSend(xhr) {
        // add preloader
        button.hide().before('<span class="spinner" style="visibility:visible;display:block;margin:0 0 0 15px"></span>');
      },
      success: function success(data) {
        // remove preloader
        button.prev().remove(); // insert settings form

        $('#' + selector).find('.inside').html(data);
      }
    });
    return false;
  }); // form submit event

  $('body').on('submit', '#rrze_statistik_settings', function () {
    var form = $(this);
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: $(this).serialize(),
      // all form fields
      beforeSend: function beforeSend(xhr) {
        // add preloader just after the submit button
        form.find('.submit').append('<span class="spinner" style="display:inline-block;float:none;visibility:visible;margin:0 0 0 15px"></span>');
      },
      success: function success(data) {
        $('[id^="rrze_statistik_widget"]').find('.inside').html(data); // show the Configure link again

        $('[id^="rrze_statistik_widget"] .edit-box.open-box').show();
      }
    });
    return false;
  });
});

/***/ }),

/***/ "./src/highcharts/highcharts.js":
/*!**************************************!*\
  !*** ./src/highcharts/highcharts.js ***!
  \**************************************/
/***/ (() => {

var currentYear = new Date().getFullYear(); //data is passed from Transfer.php

document.addEventListener("DOMContentLoaded", function (event) {
  //Check if data was passed from PHP
  if (linechartDataset === "undefined") {
    return;
  } else {
    //read needed charts from dom
    dataTypes = ["visits", "hits", "hosts", "files", "kbytes"];
    chartTypes = [];
    dataTypes.forEach(function (dataType) {
      if (document.getElementById(dataType)) {
        chartTypes.push(dataType);
      }
    }); //Process dataset and split it based on the last three years

    var firstYear = currentYear - 2;
    var secondYear = currentYear - 1;
    var thirdYear = currentYear; //Create the dataset for each datatype

    chartTypes.forEach(function (datatype) {
      var filterData = function filterData(dataset, year) {
        var output = dataset.filter(function (data) {
          return data.year === year.toString();
        });
        return output;
      };

      var outputThirdYear = filterData(linechartDataset, thirdYear);
      var outputSecondYear = filterData(linechartDataset, secondYear);
      var outputFirstYear = filterData(linechartDataset, firstYear); //Create an empty dataset array

      var generateDatasets = function generateDatasets(dataset) {
        var datasetDummy = [null, null, null, null, null, null, null, null, null, null, null, null];
        var datasetOutput = datasetDummy; //Fill the empty Array with the length of 12 (one for each month)

        dataset.forEach(function (data) {
          datasetOutput[parseInt(data.month) - 1] = parseInt(data[datatype]);
        });
        return datasetOutput;
      }; //Create three arrays, one for each year


      var datasetFirstYear = generateDatasets(outputFirstYear);
      var datasetSecondYear = generateDatasets(outputSecondYear);
      var datasetThirdYear = generateDatasets(outputThirdYear); //Load the theme colors

      var colors = Highcharts.getOptions().colors; //Create the Highcharts container for each datatype

      var chart = Highcharts.chart(datatype, {
        chart: {
          type: displayType,
          renderTo: datatype
        },
        legend: {
          symbolWidth: 40
        },
        title: {
          text: languagePackage[datatype].headline_chart
        },
        yAxis: {
          title: {
            text: languagePackage[datatype].ordinate_desc
          },
          accessibility: {
            description: languagePackage[datatype].ordinate_desc
          }
        },
        xAxis: {
          title: {
            text: abscissaTitle
          },
          accessibility: {
            description: a11yAbscissa
          },
          categories: abscissaDescriptiontext
        },
        tooltip: {
          valueSuffix: " ".concat(languagePackage[datatype].ordinate_desc)
        },
        plotOptions: {
          series: {
            cursor: "pointer"
          }
        },
        series: [{
          name: firstYear.toString(),
          data: datasetFirstYear,
          color: colors[4],
          zIndex: 0
        }, {
          name: secondYear.toString(),
          data: datasetSecondYear,
          zIndex: 1,
          dashStyle: "ShortDashDot",
          color: colors[1]
        }, {
          name: thirdYear.toString(),
          data: datasetThirdYear,
          zIndex: 2,
          dashStyle: "ShortDot",
          color: colors[2]
        }],
        //define the Export value
        exporting: {
          csv: {
            dateFormat: "%Y-%m-%d"
          }
        }
      }); //Target the copy to clipboard button within the dashboard

      document.getElementById(datatype + "-getcsv") //Add an onclick listener which stores the CSV information of the chart inside the clipboard.
      .addEventListener("click", function () {
        navigator.clipboard.writeText(chart.getCSV());
      });
    });
  }
});

/***/ }),

/***/ "./src/highcharts/themes/theme.js":
/*!****************************************!*\
  !*** ./src/highcharts/themes/theme.js ***!
  \****************************************/
/***/ (() => {

Highcharts.theme = {
  navigator: {
    series: {
      color: '#5f98cf',
      lineColor: '#5f98cf'
    }
  },
  chart: {
    backgroundColor: {
      linearGradient: [0, 0, 500, 500],
      stops: [[0, 'rgb(255, 255, 255)'], [1, 'rgb(255, 255, 255)']]
    }
  },
  title: {
    style: {
      color: '#000',
      font: 'bold 1rem "Roboto", Verdana, sans-serif'
    }
  },
  subtitle: {
    style: {
      color: '#666666',
      font: 'bold 12px "Roboto MS", Verdana, sans-serif'
    }
  },
  legend: {
    itemStyle: {
      font: '9pt Roboto MS, Verdana, sans-serif',
      color: 'black'
    },
    itemHoverStyle: {
      color: 'gray'
    }
  }
}; // Apply the theme

Highcharts.setOptions(Highcharts.theme);

/***/ }),

/***/ "./assets/sass/style.scss":
/*!********************************!*\
  !*** ./assets/sass/style.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!*********************************!*\
  !*** ./src/highcharts/index.js ***!
  \*********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _assets_sass_style_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../assets/sass/style.scss */ "./assets/sass/style.scss");
/* harmony import */ var _highcharts_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./highcharts.js */ "./src/highcharts/highcharts.js");
/* harmony import */ var _highcharts_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_highcharts_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _themes_theme_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./themes/theme.js */ "./src/highcharts/themes/theme.js");
/* harmony import */ var _themes_theme_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_themes_theme_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _ajax_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ajax.js */ "./src/highcharts/ajax.js");
/* harmony import */ var _ajax_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_ajax_js__WEBPACK_IMPORTED_MODULE_3__);




})();

/******/ })()
;
//# sourceMappingURL=highchartsIndex.js.map