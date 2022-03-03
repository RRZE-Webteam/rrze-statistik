/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/highcharts/highcharts.js":
/*!**************************************!*\
  !*** ./src/highcharts/highcharts.js ***!
  \**************************************/
/***/ (() => {

var linechartDataset;
var currentYear = new Date().getFullYear();
var _wp$i18n = wp.i18n,
    __ = _wp$i18n.__,
    _x = _wp$i18n._x,
    _n = _wp$i18n._n,
    sprintf = _wp$i18n.sprintf;
/*
Datenstruktur:
0 {monat: "3", jahr: "2020", hits: "222475", files: "188973", hosts: "2112", …}
...
*/

document.addEventListener("DOMContentLoaded", function (event) {
  console.log(linechartDataset);

  if (linechartDataset === undefined) {
    console.log("Data could not be retrieved");
    console.log(linechartDataset);
  } else {
    console.log("else");
    console.log(linechartDataset);

    var filterData = function filterData(dataset, year) {
      var output = dataset.filter(function (data) {
        return data.jahr === year.toString();
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
        datasetOutput[parseInt(data.monat) - 1] = parseInt(data.visits);
      });
      return datasetOutput;
    };

    var datasetFirstYear = generateDatasets(outputFirstYear);
    var datasetSecondYear = generateDatasets(outputSecondYear);
    var datasetThirdYear = generateDatasets(outputThirdYear);
    Highcharts.chart('container', {
      chart: {
        type: 'areaspline'
      },
      title: {
        text: 'Seitenbesucher der letzten 24 Monate'
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
        categories: [__('Jan', 'rrze-statistik'), __('Feb', 'rrze-statistik'), __('März', 'rrze-statistik'), __('April', 'rrze-statistik'), __('Mai', 'rrze-statistik'), __('Juni', 'rrze-statistik'), __('Juli', 'rrze-statistik'), __('Aug', 'rrze-statistik'), __('Sep', 'rrze-statistik'), __('Okt', 'rrze-statistik'), __('Nov', 'rrze-statistik'), __('Dez', 'rrze-statistik')]
      },
      yAxis: {
        title: {
          text: __('Besucher', 'rrze-statistik')
        }
      },
      tooltip: {
        shared: true,
        valueSuffix: __(' Besucher/Monat', 'rrze-statistik')
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

/***/ "./src/highcharts/highcharts-style.css":
/*!*********************************************!*\
  !*** ./src/highcharts/highcharts-style.css ***!
  \*********************************************/
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
/* harmony import */ var _highcharts_style_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./highcharts-style.css */ "./src/highcharts/highcharts-style.css");
/* harmony import */ var _highcharts_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./highcharts.js */ "./src/highcharts/highcharts.js");
/* harmony import */ var _highcharts_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_highcharts_js__WEBPACK_IMPORTED_MODULE_1__);


})();

/******/ })()
;
//# sourceMappingURL=highchartsIndex.js.map