/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/highcharts/highcharts.js":
/*!**************************************!*\
  !*** ./src/highcharts/highcharts.js ***!
  \**************************************/
/***/ (() => {

var currentYear = new Date().getFullYear(); //const { __, _x, _n, sprintf } = wp.i18n;

/*
Datenstruktur:
0 {monat: "3", jahr: "2020", hits: "222475", files: "188973", hosts: "2112", …}
...
*/

document.addEventListener("DOMContentLoaded", function (event) {
  if (linechartDataset === 'undefined') {
    console.log("Data could not be retrieved");
  } else {
    console.log("Dataset successfully loaded");

    var filterData = function filterData(dataset, year) {
      var output = dataset.filter(function (data) {
        return data.year === year.toString();
      });
      return output;
    };

    var outputThirdYear = filterData(linechartDataset, currentYear);
    var outputSecondYear = filterData(linechartDataset, currentYear - 1);
    var outputFirstYear = filterData(linechartDataset, currentYear - 2);

    var generateDatasets = function generateDatasets(dataset) {
      var datasetDummy = [null, null, null, null, null, null, null, null, null, null, null, null];
      var datasetOutput = datasetDummy;
      dataset.forEach(function (data) {
        datasetOutput[parseInt(data.month) - 1] = parseInt(data.visits);
      });
      return datasetOutput;
    };

    var datasetFirstYear = generateDatasets(outputFirstYear);
    var datasetSecondYear = generateDatasets(outputSecondYear);
    var datasetThirdYear = generateDatasets(outputThirdYear);
    console.log(outputThirdYear);
    console.log(outputSecondYear);
    console.log(outputFirstYear);
    Highcharts.chart('container', {
      chart: {
        type: 'areaspline'
      },
      title: {
        text: headlineDescriptiontext
      },
      legend: {
        layout: 'vertical',
        align: 'left',
        verticalAlign: 'top',
        x: 150,
        y: 100,
        floating: true,
        borderWidth: 1,
        backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF'
      },
      xAxis: {
        categories: abscissaDescriptiontext
      },
      yAxis: {
        title: {
          text: ordinateDescriptiontext
        }
      },
      tooltip: {
        shared: true,
        valueSuffix: tooltipDesc
      },
      credits: {
        enabled: false
      },
      plotOptions: {
        areaspline: {
          fillOpacity: 0.3
        }
      },
      series: [{
        name: (currentYear - 2).toString(),
        data: datasetFirstYear
      }, {
        name: (currentYear - 1).toString(),
        data: datasetSecondYear
      }, {
        name: currentYear,
        data: datasetThirdYear
      }]
    });
  }

  ;
});

/***/ }),

/***/ "./src/highcharts/themes/theme.js":
/*!****************************************!*\
  !*** ./src/highcharts/themes/theme.js ***!
  \****************************************/
/***/ (() => {

Highcharts.theme = {
  colors: ['#648fff', '#dc267f', '#ffb000', '#fe6100', '#785ef0'],
  chart: {
    backgroundColor: {
      linearGradient: [0, 0, 500, 500],
      stops: [[0, 'rgb(255, 255, 255)'], [1, 'rgb(255, 255, 255)']]
    }
  },
  title: {
    style: {
      color: '#000',
      font: 'bold 1.4rem "Roboto", Verdana, sans-serif'
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



})();

/******/ })()
;
//# sourceMappingURL=highchartsIndex.js.map