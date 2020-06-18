(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["events-featured-lazy~events-lazy"],{

/***/ "./components/events/EventsAPI.js":
/*!****************************************!*\
  !*** ./components/events/EventsAPI.js ***!
  \****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
/* harmony import */ var axios__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(axios__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var moment__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! moment */ "./node_modules/moment/moment.js");
/* harmony import */ var moment__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(moment__WEBPACK_IMPORTED_MODULE_2__);


function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }

  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}

function _asyncToGenerator(fn) {
  return function () {
    var self = this,
        args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);

      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }

      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }

      _next(undefined);
    });
  };
}

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}



/**
 * Provides an abstraction for the BSD events data service.
 */

var EventsAPI =
/*#__PURE__*/
function () {
  /**
   * EventsAPI constructor function.
   *
   * @param {object} properties
   *   A object of default properties for this instance.
   */
  function EventsAPI() {
    var properties = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
      endpoint: 'http://bsd-data.dev.uchicago.edu/api/event_index',
      instance: 'pritzker'
    };

    _classCallCheck(this, EventsAPI);

    this.endpoint = properties.endpoint;
    this.instance = properties.instance;
  }

  _createClass(EventsAPI, [{
    key: "getAllEvents",
    value: function () {
      var _getAllEvents = _asyncToGenerator(
      /*#__PURE__*/
      _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee() {
        var results, url, response;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                results = {}; // Build the request url.

                url = this.endpoint;
                url += '?_bsdInstanceId=' + this.instance;
                _context.prev = 3;
                _context.next = 6;
                return axios__WEBPACK_IMPORTED_MODULE_1___default.a.get(url);

              case 6:
                response = _context.sent;
                results = response.data.data;
                _context.next = 13;
                break;

              case 10:
                _context.prev = 10;
                _context.t0 = _context["catch"](3);
                console.log(_context.t0);

              case 13:
                return _context.abrupt("return", results);

              case 14:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this, [[3, 10]]);
      }));

      function getAllEvents() {
        return _getAllEvents.apply(this, arguments);
      }

      return getAllEvents;
    }()
    /**
     * Returns featured events from the BSD event API.
     *
     * @param {int} limit
     *   The maximum number of results that can be returned. Zero means no limit.
     *
     * @returns {Promise<{images: Array, results: Array}>}
     */

  }, {
    key: "getFeaturedEvents",
    value: function () {
      var _getFeaturedEvents = _asyncToGenerator(
      /*#__PURE__*/
      _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee2() {
        var limit,
            url,
            resultSet,
            response,
            images,
            _args2 = arguments;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                limit = _args2.length > 0 && _args2[0] !== undefined ? _args2[0] : 0; // Build the request url.

                url = this.endpoint;
                url += '?_bsdInstanceId=' + this.instance;
                url += '&filter[date-group][group][conjunction]=AND';
                url += '&filter[event-start-filter][condition][path]=field_event_dates.value';
                url += '&filter[event-start-filter][condition][operator]=>';
                url += '&filter[event-start-filter][condition][value]=' + moment__WEBPACK_IMPORTED_MODULE_2___default()().format('YYYY-MM-DD');
                url += '&filter[event-start-filter][condition][memberOf]=date-group';
                url += '&filter[event-featured-filter][condition][path]=field_featured';
                url += '&filter[event-featured-filter][condition][value]=1';
                url += '&filter[event-featured-filter][condition][memberOf]=date-group';

                if (limit > 0) {
                  url += '&page[limit]=' + limit;
                }

                url += '&include=field_event_image';
                url += '&sort[event-sort][path]=field_event_dates.value';
                url += '&sort[event-sort][direction]=ASC'; // Initialize the result set.

                resultSet = {
                  images: [],
                  results: []
                }; // Make the API request and parse the results.

                _context2.prev = 16;
                _context2.next = 19;
                return axios__WEBPACK_IMPORTED_MODULE_1___default.a.get(url, {
                  headers: {
                    'Accept': 'application/vnd.api+json',
                    'Content-Type': 'application/vnd.api+json'
                  }
                });

              case 19:
                response = _context2.sent;
                images = typeof response.data.included !== 'undefined' ? this._formatImages(response.data.included) : [];
                resultSet = {
                  images: images,
                  results: this._normalize(response.data.data)
                };
                _context2.next = 27;
                break;

              case 24:
                _context2.prev = 24;
                _context2.t0 = _context2["catch"](16);
                console.log(_context2.t0);

              case 27:
                return _context2.abrupt("return", resultSet);

              case 28:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2, this, [[16, 24]]);
      }));

      function getFeaturedEvents() {
        return _getFeaturedEvents.apply(this, arguments);
      }

      return getFeaturedEvents;
    }()
    /**
     * Returns a list of events based on the given event ids.
     *
     * @param {Array} eventIds
     *   An array of event guids.
     *
     * @returns {Promise<{images: Array, results: Array}>}
     */

  }, {
    key: "getEventsById",
    value: function () {
      var _getEventsById = _asyncToGenerator(
      /*#__PURE__*/
      _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee3(eventIds) {
        var url, i, eventId, resultSet, response, images;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                // Build the request url.
                url = this.endpoint;
                url += '?_bsdInstanceId=' + this.instance;
                url += '&filter[id-group][group][conjunction]=OR';

                for (i = 0; i < eventIds.length; i++) {
                  eventId = eventIds[i];
                  url += "&filter[event-id-filter-".concat(i, "][condition][path]=id");
                  url += "&filter[event-id-filter-".concat(i, "][condition][value]=").concat(eventId);
                  url += "&filter[event-id-filter-".concat(i, "][condition][memberOf]=id-group");
                }

                url += '&include=field_event_image';
                url += '&sort[event-sort][path]=field_event_dates.value';
                url += '&sort[event-sort][direction]=ASC'; // Initialize the result set.

                resultSet = {
                  images: [],
                  results: []
                }; // Make the API request and parse the results.

                _context3.prev = 8;
                _context3.next = 11;
                return axios__WEBPACK_IMPORTED_MODULE_1___default.a.get(url, {
                  headers: {
                    'Accept': 'application/vnd.api+json',
                    'Content-Type': 'application/vnd.api+json'
                  }
                });

              case 11:
                response = _context3.sent;
                images = typeof response.data.included !== 'undefined' ? this._formatImages(response.data.included) : [];
                resultSet = {
                  images: images,
                  results: this._normalize(response.data.data)
                };
                _context3.next = 19;
                break;

              case 16:
                _context3.prev = 16;
                _context3.t0 = _context3["catch"](8);
                console.log(_context3.t0);

              case 19:
                return _context3.abrupt("return", resultSet);

              case 20:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3, this, [[8, 16]]);
      }));

      function getEventsById(_x) {
        return _getEventsById.apply(this, arguments);
      }

      return getEventsById;
    }()
    /**
     * Returns a list of events within the given date range.
     *
     * @param {string} startDate
     *    Start Date ('YYYY-MM-DD')
     *
     * @param {string} endDate
     *    End Date ('YYYY-MM-DD')
     *
     * @returns {Promise<{results: Array}>}
     */

  }, {
    key: "getEventsByRange",
    value: function () {
      var _getEventsByRange = _asyncToGenerator(
      /*#__PURE__*/
      _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee4(startDate, endDate) {
        var url, results, response;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee4$(_context4) {
          while (1) {
            switch (_context4.prev = _context4.next) {
              case 0:
                url = this.endpoint;
                url += '?_bsdInstanceId=' + this.instance;
                url += '&filter[date-group][group][conjunction]=AND';
                url += '&filter[event-start-filter][condition][path]=field_event_dates.value';
                url += '&filter[event-start-filter][condition][operator]=>';
                url += '&filter[event-start-filter][condition][value]=' + startDate;
                url += '&filter[event-start-filter][condition][memberOf]=date-group';
                url += '&filter[event-end-filter][condition][path]=field_event_dates.end_value';
                url += '&filter[event-end-filter][condition][operator]=<';
                url += '&filter[event-end-filter][condition][value]=' + endDate;
                url += '&filter[event-end-filter][condition][memberOf]=date-group';
                url += '&sort[event-sort][path]=field_event_dates.value';
                url += '&sort[event-sort][direction]=ASC'; // Initialize the result set.

                results = {}; // Make the API request and parse the results.

                _context4.prev = 14;
                _context4.next = 17;
                return axios__WEBPACK_IMPORTED_MODULE_1___default.a.get(url, {
                  headers: {
                    'Accept': 'application/vnd.api+json',
                    'Content-Type': 'application/vnd.api+json'
                  }
                });

              case 17:
                response = _context4.sent;
                results = this._normalize(response.data.data);
                _context4.next = 24;
                break;

              case 21:
                _context4.prev = 21;
                _context4.t0 = _context4["catch"](14);
                console.log(_context4.t0);

              case 24:
                return _context4.abrupt("return", results);

              case 25:
              case "end":
                return _context4.stop();
            }
          }
        }, _callee4, this, [[14, 21]]);
      }));

      function getEventsByRange(_x2, _x3) {
        return _getEventsByRange.apply(this, arguments);
      }

      return getEventsByRange;
    }()
    /**
     * Returns a list of events that match the given query.
     *
     * @param {string} query
     *    Search Query
     *
     * @returns {Promise<{results: Array}>}
     */

  }, {
    key: "getEventsFromQuery",
    value: function () {
      var _getEventsFromQuery = _asyncToGenerator(
      /*#__PURE__*/
      _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee5(query) {
        var url, results, response;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee5$(_context5) {
          while (1) {
            switch (_context5.prev = _context5.next) {
              case 0:
                url = this.endpoint; // query the title and description fields from the API

                url += '?_bsdInstanceId=' + this.instance;
                url += '&filter[search-group][group][conjunction]=OR';
                url += '&filter[title-search][condition][path]=title';
                url += '&filter[title-search][condition][operator]=CONTAINS';
                url += '&filter[title-search][condition][value]=' + query;
                url += '&filter[title-search][condition][memberOf]=search-group';
                url += '&filter[desc-search][condition][path]=field_description';
                url += '&filter[desc-search][condition][operator]=CONTAINS';
                url += '&filter[desc-search][condition][value]=' + query;
                url += '&filter[desc-search][condition][memberOf]=search-group';
                url += '&filter[event-start-filter][condition][path]=field_event_dates.value';
                url += '&filter[event-start-filter][condition][operator]=>';
                url += '&filter[event-start-filter][condition][value]=' + moment__WEBPACK_IMPORTED_MODULE_2___default()().format('YYYY-MM-DD');
                url += '&sort[event-sort][path]=field_event_dates.value';
                url += '&sort[event-sort][direction]=ASC'; // Initialize the result set.

                results = {}; // Make the API request and parse the results.

                _context5.prev = 17;
                _context5.next = 20;
                return axios__WEBPACK_IMPORTED_MODULE_1___default.a.get(url, {
                  headers: {
                    'Accept': 'application/vnd.api+json',
                    'Content-Type': 'application/vnd.api+json'
                  }
                });

              case 20:
                response = _context5.sent;
                results = this._normalize(response.data.data);
                _context5.next = 27;
                break;

              case 24:
                _context5.prev = 24;
                _context5.t0 = _context5["catch"](17);
                console.log(_context5.t0);

              case 27:
                return _context5.abrupt("return", results);

              case 28:
              case "end":
                return _context5.stop();
            }
          }
        }, _callee5, this, [[17, 24]]);
      }));

      function getEventsFromQuery(_x4) {
        return _getEventsFromQuery.apply(this, arguments);
      }

      return getEventsFromQuery;
    }()
    /**
     * Returns a list of events that match the given query.
     *
     * @returns {string} Date of last event
     */

  }, {
    key: "getLastEvent",
    value: function () {
      var _getLastEvent = _asyncToGenerator(
      /*#__PURE__*/
      _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee6() {
        var url, result, response;
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee6$(_context6) {
          while (1) {
            switch (_context6.prev = _context6.next) {
              case 0:
                url = this.endpoint;
                url += '?_bsdInstanceId=' + this.instance;
                url += '&sort[sort-date][path]=field_event_dates.value';
                url += '&sort[sort-date][direction]=DESC';
                url += '&page[limit]=1';
                url += '&fields[node--event]=field_event_dates'; // Initialize the result set.

                result = ''; // Make the API request and parse the results.

                _context6.prev = 7;
                _context6.next = 10;
                return axios__WEBPACK_IMPORTED_MODULE_1___default.a.get(url, {
                  headers: {
                    'Accept': 'application/vnd.api+json',
                    'Content-Type': 'application/vnd.api+json'
                  }
                });

              case 10:
                response = _context6.sent;
                result = response.data.data[0].attributes.field_event_dates.value;
                _context6.next = 17;
                break;

              case 14:
                _context6.prev = 14;
                _context6.t0 = _context6["catch"](7);
                console.log(_context6.t0);

              case 17:
                return _context6.abrupt("return", result);

              case 18:
              case "end":
                return _context6.stop();
            }
          }
        }, _callee6, this, [[7, 14]]);
      }));

      function getLastEvent() {
        return _getLastEvent.apply(this, arguments);
      }

      return getLastEvent;
    }()
    /**
     * Takes raw data from the BSD API and converts it to a normalized format.
     *
     * @param {Array} list
     *   Array of event data.
     *
     * @returns {Array}
     *
     * @private
     */

  }, {
    key: "_normalize",
    value: function _normalize(list) {
      // loop through events and convert into smaller, more
      // manageable fields
      var newList = []; // map of new keys and their location
      // within the original JSON object

      list.forEach(function (item) {
        var newItem = {};
        newItem.id = item.id;
        var _item$attributes = item.attributes;
        newItem.drupalId = _item$attributes.drupal_internal__nid;
        newItem.allDay = _item$attributes.field_all_day;
        newItem.contact = _item$attributes.field_contact;
        var _item$attributes$fiel = _item$attributes.field_event_dates;
        newItem.start = _item$attributes$fiel.value;
        newItem.end = _item$attributes$fiel.end_value;
        newItem.caption = _item$attributes.field_event_image_caption;
        newItem.status = _item$attributes.field_event_status;
        newItem.featured = _item$attributes.field_featured;
        newItem.location = _item$attributes.field_location;
        newItem.title = _item$attributes.title;
        newItem.pathAlias = _item$attributes.path.alias;
        newItem.link = item.links.self.href; // Since an image is optional, the data is not always present.

        if (item.relationships.field_event_image.data !== null) {
          newItem.imgId = item.relationships.field_event_image.data.id;
        }

        newList.push(newItem);
      });
      return newList;
    }
    /**
     * Converts raw image data into a more normalized format.
     *
     * @param {Array} images
     *   An array of event images.
     *
     * @returns {Array}
     *
     * @private
     */

  }, {
    key: "_formatImages",
    value: function _formatImages(images) {
      // create separate array of images from data
      var newList = [];

      if (images) {
        images.forEach(function (img) {
          newList[img.id] = img.links.event_image.href;
        });
      }

      return newList;
    }
  }]);

  return EventsAPI;
}();

/* harmony default export */ __webpack_exports__["default"] = (EventsAPI);

/***/ }),

/***/ "./node_modules/moment/locale sync recursive ^\\.\\/.*$":
/*!**************************************************!*\
  !*** ./node_modules/moment/locale sync ^\.\/.*$ ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var map = {
	"./af": "./node_modules/moment/locale/af.js",
	"./af.js": "./node_modules/moment/locale/af.js",
	"./ar": "./node_modules/moment/locale/ar.js",
	"./ar-dz": "./node_modules/moment/locale/ar-dz.js",
	"./ar-dz.js": "./node_modules/moment/locale/ar-dz.js",
	"./ar-kw": "./node_modules/moment/locale/ar-kw.js",
	"./ar-kw.js": "./node_modules/moment/locale/ar-kw.js",
	"./ar-ly": "./node_modules/moment/locale/ar-ly.js",
	"./ar-ly.js": "./node_modules/moment/locale/ar-ly.js",
	"./ar-ma": "./node_modules/moment/locale/ar-ma.js",
	"./ar-ma.js": "./node_modules/moment/locale/ar-ma.js",
	"./ar-sa": "./node_modules/moment/locale/ar-sa.js",
	"./ar-sa.js": "./node_modules/moment/locale/ar-sa.js",
	"./ar-tn": "./node_modules/moment/locale/ar-tn.js",
	"./ar-tn.js": "./node_modules/moment/locale/ar-tn.js",
	"./ar.js": "./node_modules/moment/locale/ar.js",
	"./az": "./node_modules/moment/locale/az.js",
	"./az.js": "./node_modules/moment/locale/az.js",
	"./be": "./node_modules/moment/locale/be.js",
	"./be.js": "./node_modules/moment/locale/be.js",
	"./bg": "./node_modules/moment/locale/bg.js",
	"./bg.js": "./node_modules/moment/locale/bg.js",
	"./bm": "./node_modules/moment/locale/bm.js",
	"./bm.js": "./node_modules/moment/locale/bm.js",
	"./bn": "./node_modules/moment/locale/bn.js",
	"./bn.js": "./node_modules/moment/locale/bn.js",
	"./bo": "./node_modules/moment/locale/bo.js",
	"./bo.js": "./node_modules/moment/locale/bo.js",
	"./br": "./node_modules/moment/locale/br.js",
	"./br.js": "./node_modules/moment/locale/br.js",
	"./bs": "./node_modules/moment/locale/bs.js",
	"./bs.js": "./node_modules/moment/locale/bs.js",
	"./ca": "./node_modules/moment/locale/ca.js",
	"./ca.js": "./node_modules/moment/locale/ca.js",
	"./cs": "./node_modules/moment/locale/cs.js",
	"./cs.js": "./node_modules/moment/locale/cs.js",
	"./cv": "./node_modules/moment/locale/cv.js",
	"./cv.js": "./node_modules/moment/locale/cv.js",
	"./cy": "./node_modules/moment/locale/cy.js",
	"./cy.js": "./node_modules/moment/locale/cy.js",
	"./da": "./node_modules/moment/locale/da.js",
	"./da.js": "./node_modules/moment/locale/da.js",
	"./de": "./node_modules/moment/locale/de.js",
	"./de-at": "./node_modules/moment/locale/de-at.js",
	"./de-at.js": "./node_modules/moment/locale/de-at.js",
	"./de-ch": "./node_modules/moment/locale/de-ch.js",
	"./de-ch.js": "./node_modules/moment/locale/de-ch.js",
	"./de.js": "./node_modules/moment/locale/de.js",
	"./dv": "./node_modules/moment/locale/dv.js",
	"./dv.js": "./node_modules/moment/locale/dv.js",
	"./el": "./node_modules/moment/locale/el.js",
	"./el.js": "./node_modules/moment/locale/el.js",
	"./en-SG": "./node_modules/moment/locale/en-SG.js",
	"./en-SG.js": "./node_modules/moment/locale/en-SG.js",
	"./en-au": "./node_modules/moment/locale/en-au.js",
	"./en-au.js": "./node_modules/moment/locale/en-au.js",
	"./en-ca": "./node_modules/moment/locale/en-ca.js",
	"./en-ca.js": "./node_modules/moment/locale/en-ca.js",
	"./en-gb": "./node_modules/moment/locale/en-gb.js",
	"./en-gb.js": "./node_modules/moment/locale/en-gb.js",
	"./en-ie": "./node_modules/moment/locale/en-ie.js",
	"./en-ie.js": "./node_modules/moment/locale/en-ie.js",
	"./en-il": "./node_modules/moment/locale/en-il.js",
	"./en-il.js": "./node_modules/moment/locale/en-il.js",
	"./en-nz": "./node_modules/moment/locale/en-nz.js",
	"./en-nz.js": "./node_modules/moment/locale/en-nz.js",
	"./eo": "./node_modules/moment/locale/eo.js",
	"./eo.js": "./node_modules/moment/locale/eo.js",
	"./es": "./node_modules/moment/locale/es.js",
	"./es-do": "./node_modules/moment/locale/es-do.js",
	"./es-do.js": "./node_modules/moment/locale/es-do.js",
	"./es-us": "./node_modules/moment/locale/es-us.js",
	"./es-us.js": "./node_modules/moment/locale/es-us.js",
	"./es.js": "./node_modules/moment/locale/es.js",
	"./et": "./node_modules/moment/locale/et.js",
	"./et.js": "./node_modules/moment/locale/et.js",
	"./eu": "./node_modules/moment/locale/eu.js",
	"./eu.js": "./node_modules/moment/locale/eu.js",
	"./fa": "./node_modules/moment/locale/fa.js",
	"./fa.js": "./node_modules/moment/locale/fa.js",
	"./fi": "./node_modules/moment/locale/fi.js",
	"./fi.js": "./node_modules/moment/locale/fi.js",
	"./fo": "./node_modules/moment/locale/fo.js",
	"./fo.js": "./node_modules/moment/locale/fo.js",
	"./fr": "./node_modules/moment/locale/fr.js",
	"./fr-ca": "./node_modules/moment/locale/fr-ca.js",
	"./fr-ca.js": "./node_modules/moment/locale/fr-ca.js",
	"./fr-ch": "./node_modules/moment/locale/fr-ch.js",
	"./fr-ch.js": "./node_modules/moment/locale/fr-ch.js",
	"./fr.js": "./node_modules/moment/locale/fr.js",
	"./fy": "./node_modules/moment/locale/fy.js",
	"./fy.js": "./node_modules/moment/locale/fy.js",
	"./ga": "./node_modules/moment/locale/ga.js",
	"./ga.js": "./node_modules/moment/locale/ga.js",
	"./gd": "./node_modules/moment/locale/gd.js",
	"./gd.js": "./node_modules/moment/locale/gd.js",
	"./gl": "./node_modules/moment/locale/gl.js",
	"./gl.js": "./node_modules/moment/locale/gl.js",
	"./gom-latn": "./node_modules/moment/locale/gom-latn.js",
	"./gom-latn.js": "./node_modules/moment/locale/gom-latn.js",
	"./gu": "./node_modules/moment/locale/gu.js",
	"./gu.js": "./node_modules/moment/locale/gu.js",
	"./he": "./node_modules/moment/locale/he.js",
	"./he.js": "./node_modules/moment/locale/he.js",
	"./hi": "./node_modules/moment/locale/hi.js",
	"./hi.js": "./node_modules/moment/locale/hi.js",
	"./hr": "./node_modules/moment/locale/hr.js",
	"./hr.js": "./node_modules/moment/locale/hr.js",
	"./hu": "./node_modules/moment/locale/hu.js",
	"./hu.js": "./node_modules/moment/locale/hu.js",
	"./hy-am": "./node_modules/moment/locale/hy-am.js",
	"./hy-am.js": "./node_modules/moment/locale/hy-am.js",
	"./id": "./node_modules/moment/locale/id.js",
	"./id.js": "./node_modules/moment/locale/id.js",
	"./is": "./node_modules/moment/locale/is.js",
	"./is.js": "./node_modules/moment/locale/is.js",
	"./it": "./node_modules/moment/locale/it.js",
	"./it-ch": "./node_modules/moment/locale/it-ch.js",
	"./it-ch.js": "./node_modules/moment/locale/it-ch.js",
	"./it.js": "./node_modules/moment/locale/it.js",
	"./ja": "./node_modules/moment/locale/ja.js",
	"./ja.js": "./node_modules/moment/locale/ja.js",
	"./jv": "./node_modules/moment/locale/jv.js",
	"./jv.js": "./node_modules/moment/locale/jv.js",
	"./ka": "./node_modules/moment/locale/ka.js",
	"./ka.js": "./node_modules/moment/locale/ka.js",
	"./kk": "./node_modules/moment/locale/kk.js",
	"./kk.js": "./node_modules/moment/locale/kk.js",
	"./km": "./node_modules/moment/locale/km.js",
	"./km.js": "./node_modules/moment/locale/km.js",
	"./kn": "./node_modules/moment/locale/kn.js",
	"./kn.js": "./node_modules/moment/locale/kn.js",
	"./ko": "./node_modules/moment/locale/ko.js",
	"./ko.js": "./node_modules/moment/locale/ko.js",
	"./ku": "./node_modules/moment/locale/ku.js",
	"./ku.js": "./node_modules/moment/locale/ku.js",
	"./ky": "./node_modules/moment/locale/ky.js",
	"./ky.js": "./node_modules/moment/locale/ky.js",
	"./lb": "./node_modules/moment/locale/lb.js",
	"./lb.js": "./node_modules/moment/locale/lb.js",
	"./lo": "./node_modules/moment/locale/lo.js",
	"./lo.js": "./node_modules/moment/locale/lo.js",
	"./lt": "./node_modules/moment/locale/lt.js",
	"./lt.js": "./node_modules/moment/locale/lt.js",
	"./lv": "./node_modules/moment/locale/lv.js",
	"./lv.js": "./node_modules/moment/locale/lv.js",
	"./me": "./node_modules/moment/locale/me.js",
	"./me.js": "./node_modules/moment/locale/me.js",
	"./mi": "./node_modules/moment/locale/mi.js",
	"./mi.js": "./node_modules/moment/locale/mi.js",
	"./mk": "./node_modules/moment/locale/mk.js",
	"./mk.js": "./node_modules/moment/locale/mk.js",
	"./ml": "./node_modules/moment/locale/ml.js",
	"./ml.js": "./node_modules/moment/locale/ml.js",
	"./mn": "./node_modules/moment/locale/mn.js",
	"./mn.js": "./node_modules/moment/locale/mn.js",
	"./mr": "./node_modules/moment/locale/mr.js",
	"./mr.js": "./node_modules/moment/locale/mr.js",
	"./ms": "./node_modules/moment/locale/ms.js",
	"./ms-my": "./node_modules/moment/locale/ms-my.js",
	"./ms-my.js": "./node_modules/moment/locale/ms-my.js",
	"./ms.js": "./node_modules/moment/locale/ms.js",
	"./mt": "./node_modules/moment/locale/mt.js",
	"./mt.js": "./node_modules/moment/locale/mt.js",
	"./my": "./node_modules/moment/locale/my.js",
	"./my.js": "./node_modules/moment/locale/my.js",
	"./nb": "./node_modules/moment/locale/nb.js",
	"./nb.js": "./node_modules/moment/locale/nb.js",
	"./ne": "./node_modules/moment/locale/ne.js",
	"./ne.js": "./node_modules/moment/locale/ne.js",
	"./nl": "./node_modules/moment/locale/nl.js",
	"./nl-be": "./node_modules/moment/locale/nl-be.js",
	"./nl-be.js": "./node_modules/moment/locale/nl-be.js",
	"./nl.js": "./node_modules/moment/locale/nl.js",
	"./nn": "./node_modules/moment/locale/nn.js",
	"./nn.js": "./node_modules/moment/locale/nn.js",
	"./pa-in": "./node_modules/moment/locale/pa-in.js",
	"./pa-in.js": "./node_modules/moment/locale/pa-in.js",
	"./pl": "./node_modules/moment/locale/pl.js",
	"./pl.js": "./node_modules/moment/locale/pl.js",
	"./pt": "./node_modules/moment/locale/pt.js",
	"./pt-br": "./node_modules/moment/locale/pt-br.js",
	"./pt-br.js": "./node_modules/moment/locale/pt-br.js",
	"./pt.js": "./node_modules/moment/locale/pt.js",
	"./ro": "./node_modules/moment/locale/ro.js",
	"./ro.js": "./node_modules/moment/locale/ro.js",
	"./ru": "./node_modules/moment/locale/ru.js",
	"./ru.js": "./node_modules/moment/locale/ru.js",
	"./sd": "./node_modules/moment/locale/sd.js",
	"./sd.js": "./node_modules/moment/locale/sd.js",
	"./se": "./node_modules/moment/locale/se.js",
	"./se.js": "./node_modules/moment/locale/se.js",
	"./si": "./node_modules/moment/locale/si.js",
	"./si.js": "./node_modules/moment/locale/si.js",
	"./sk": "./node_modules/moment/locale/sk.js",
	"./sk.js": "./node_modules/moment/locale/sk.js",
	"./sl": "./node_modules/moment/locale/sl.js",
	"./sl.js": "./node_modules/moment/locale/sl.js",
	"./sq": "./node_modules/moment/locale/sq.js",
	"./sq.js": "./node_modules/moment/locale/sq.js",
	"./sr": "./node_modules/moment/locale/sr.js",
	"./sr-cyrl": "./node_modules/moment/locale/sr-cyrl.js",
	"./sr-cyrl.js": "./node_modules/moment/locale/sr-cyrl.js",
	"./sr.js": "./node_modules/moment/locale/sr.js",
	"./ss": "./node_modules/moment/locale/ss.js",
	"./ss.js": "./node_modules/moment/locale/ss.js",
	"./sv": "./node_modules/moment/locale/sv.js",
	"./sv.js": "./node_modules/moment/locale/sv.js",
	"./sw": "./node_modules/moment/locale/sw.js",
	"./sw.js": "./node_modules/moment/locale/sw.js",
	"./ta": "./node_modules/moment/locale/ta.js",
	"./ta.js": "./node_modules/moment/locale/ta.js",
	"./te": "./node_modules/moment/locale/te.js",
	"./te.js": "./node_modules/moment/locale/te.js",
	"./tet": "./node_modules/moment/locale/tet.js",
	"./tet.js": "./node_modules/moment/locale/tet.js",
	"./tg": "./node_modules/moment/locale/tg.js",
	"./tg.js": "./node_modules/moment/locale/tg.js",
	"./th": "./node_modules/moment/locale/th.js",
	"./th.js": "./node_modules/moment/locale/th.js",
	"./tl-ph": "./node_modules/moment/locale/tl-ph.js",
	"./tl-ph.js": "./node_modules/moment/locale/tl-ph.js",
	"./tlh": "./node_modules/moment/locale/tlh.js",
	"./tlh.js": "./node_modules/moment/locale/tlh.js",
	"./tr": "./node_modules/moment/locale/tr.js",
	"./tr.js": "./node_modules/moment/locale/tr.js",
	"./tzl": "./node_modules/moment/locale/tzl.js",
	"./tzl.js": "./node_modules/moment/locale/tzl.js",
	"./tzm": "./node_modules/moment/locale/tzm.js",
	"./tzm-latn": "./node_modules/moment/locale/tzm-latn.js",
	"./tzm-latn.js": "./node_modules/moment/locale/tzm-latn.js",
	"./tzm.js": "./node_modules/moment/locale/tzm.js",
	"./ug-cn": "./node_modules/moment/locale/ug-cn.js",
	"./ug-cn.js": "./node_modules/moment/locale/ug-cn.js",
	"./uk": "./node_modules/moment/locale/uk.js",
	"./uk.js": "./node_modules/moment/locale/uk.js",
	"./ur": "./node_modules/moment/locale/ur.js",
	"./ur.js": "./node_modules/moment/locale/ur.js",
	"./uz": "./node_modules/moment/locale/uz.js",
	"./uz-latn": "./node_modules/moment/locale/uz-latn.js",
	"./uz-latn.js": "./node_modules/moment/locale/uz-latn.js",
	"./uz.js": "./node_modules/moment/locale/uz.js",
	"./vi": "./node_modules/moment/locale/vi.js",
	"./vi.js": "./node_modules/moment/locale/vi.js",
	"./x-pseudo": "./node_modules/moment/locale/x-pseudo.js",
	"./x-pseudo.js": "./node_modules/moment/locale/x-pseudo.js",
	"./yo": "./node_modules/moment/locale/yo.js",
	"./yo.js": "./node_modules/moment/locale/yo.js",
	"./zh-cn": "./node_modules/moment/locale/zh-cn.js",
	"./zh-cn.js": "./node_modules/moment/locale/zh-cn.js",
	"./zh-hk": "./node_modules/moment/locale/zh-hk.js",
	"./zh-hk.js": "./node_modules/moment/locale/zh-hk.js",
	"./zh-tw": "./node_modules/moment/locale/zh-tw.js",
	"./zh-tw.js": "./node_modules/moment/locale/zh-tw.js"
};


function webpackContext(req) {
	var id = webpackContextResolve(req);
	return __webpack_require__(id);
}
function webpackContextResolve(req) {
	if(!__webpack_require__.o(map, req)) {
		var e = new Error("Cannot find module '" + req + "'");
		e.code = 'MODULE_NOT_FOUND';
		throw e;
	}
	return map[req];
}
webpackContext.keys = function webpackContextKeys() {
	return Object.keys(map);
};
webpackContext.resolve = webpackContextResolve;
module.exports = webpackContext;
webpackContext.id = "./node_modules/moment/locale sync recursive ^\\.\\/.*$";

/***/ })

}]);
//# sourceMappingURL=events-featured-lazy~events-lazy.js.map