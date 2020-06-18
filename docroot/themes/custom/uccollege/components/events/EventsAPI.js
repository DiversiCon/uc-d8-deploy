import axios from 'axios';
import moment from 'moment';

/**
 * Provides an abstraction for the BSD events data service.
 */
class EventsAPI {
  /**
   * EventsAPI constructor function.
   *
   * @param {object} properties
   *   A object of default properties for this instance.
   */
  constructor(properties = { endpoint: 'http://bsd-data.dev.uchicago.edu/api/event_index', instance: 'pritzker' }) {
    this.endpoint = properties.endpoint;
    this.instance = properties.instance;
  }

  async getAllEvents() {
    let results = {};
    // Build the request url.
    let url = this.endpoint;
    url += '?_bsdInstanceId=' + this.instance;

    try {
      const response = await axios.get(url);
      results = response.data.data;
    } catch (error) {
      console.log(error);
    }
    return results;
  }

  /**
   * Returns featured events from the BSD event API.
   *
   * @param {int} limit
   *   The maximum number of results that can be returned. Zero means no limit.
   *
   * @returns {Promise<{images: Array, results: Array}>}
   */
  async getFeaturedEvents(limit = 0) {
    // Build the request url.
    let url = this.endpoint;
    url += '?_bsdInstanceId=' + this.instance;

    url += '&filter[date-group][group][conjunction]=AND';

    url += '&filter[event-start-filter][condition][path]=field_event_dates.value';
    url += '&filter[event-start-filter][condition][operator]=>';
    url += '&filter[event-start-filter][condition][value]=' + moment().format('YYYY-MM-DD');
    url += '&filter[event-start-filter][condition][memberOf]=date-group';

    url += '&filter[event-featured-filter][condition][path]=field_featured';
    url += '&filter[event-featured-filter][condition][value]=1';
    url += '&filter[event-featured-filter][condition][memberOf]=date-group';

    if (limit > 0) {
      url += '&page[limit]=' + limit;
    }

    url += '&include=field_event_image';
    url += '&sort[event-sort][path]=field_event_dates.value';
    url += '&sort[event-sort][direction]=ASC';

    // Initialize the result set.
    let resultSet = {
      images: [],
      results: [],
    };

    // Make the API request and parse the results.
    try {
      const response = await axios.get(url, {
        headers: {
          'Accept': 'application/vnd.api+json',
          'Content-Type': 'application/vnd.api+json',
        },
      });
      const images = (typeof (response.data.included) !== 'undefined') ? this._formatImages(response.data.included) : [];
      resultSet = {
        images,
        results: this._normalize(response.data.data),
      };
    } catch (error) {
      console.log(error);
    }

    // Return the result set.
    return resultSet;
  }

  /**
   * Returns a list of events based on the given event ids.
   *
   * @param {Array} eventIds
   *   An array of event guids.
   *
   * @returns {Promise<{images: Array, results: Array}>}
   */
  async getEventsById(eventIds) {
    // Build the request url.
    let url = this.endpoint;
    url += '?_bsdInstanceId=' + this.instance;

    url += '&filter[id-group][group][conjunction]=OR';

    for (let i = 0; i < eventIds.length; i++) {
      const eventId = eventIds[i];
      url += `&filter[event-id-filter-${i}][condition][path]=id`;
      url += `&filter[event-id-filter-${i}][condition][value]=${eventId}`;
      url += `&filter[event-id-filter-${i}][condition][memberOf]=id-group`;
    }

    url += '&include=field_event_image';
    url += '&sort[event-sort][path]=field_event_dates.value';
    url += '&sort[event-sort][direction]=ASC';

    // Initialize the result set.
    let resultSet = {
      images: [],
      results: [],
    };

    // Make the API request and parse the results.
    try {
      const response = await axios.get(url, {
        headers: {
          'Accept': 'application/vnd.api+json',
          'Content-Type': 'application/vnd.api+json',
        },
      });

      const images = (typeof (response.data.included) !== 'undefined') ? this._formatImages(response.data.included) : [];
      resultSet = {
        images,
        results: this._normalize(response.data.data),
      };
    } catch (error) {
      console.log(error);
    }

    // Return the result set.
    return resultSet;
  }

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
  async getEventsByRange(startDate, endDate) {
    let url = this.endpoint;
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
    url += '&sort[event-sort][direction]=ASC';


    // Initialize the result set.
    let results = {};

    // Make the API request and parse the results.
    try {
      const response = await axios.get(url, {
        headers: {
          'Accept': 'application/vnd.api+json',
          'Content-Type': 'application/vnd.api+json',
        },
      });

      results = this._normalize(response.data.data);
    } catch (error) {
      console.log(error);
    }

    // Return the result set.
    return results;
  }

  /**
   * Returns a list of events that match the given query.
   *
   * @param {string} query
   *    Search Query
   *
   * @returns {Promise<{results: Array}>}
   */
  async getEventsFromQuery(query) {
    let url = this.endpoint;
    // query the title and description fields from the API
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
    url += '&filter[event-start-filter][condition][value]=' + moment().format('YYYY-MM-DD');

    url += '&sort[event-sort][path]=field_event_dates.value';
    url += '&sort[event-sort][direction]=ASC';


    // Initialize the result set.
    let results = {};

    // Make the API request and parse the results.
    try {
      const response = await axios.get(url, {
        headers: {
          'Accept': 'application/vnd.api+json',
          'Content-Type': 'application/vnd.api+json',
        },
      });

      results = this._normalize(response.data.data);
    } catch (error) {
      console.log(error);
    }

    // Return the result set.
    return results;
  }


  /**
   * Returns a list of events that match the given query.
   *
   * @returns {string} Date of last event
   */
  async getLastEvent() {
    let url = this.endpoint;
    url += '?_bsdInstanceId=' + this.instance;
    url += '&sort[sort-date][path]=field_event_dates.value';
    url += '&sort[sort-date][direction]=DESC';
    url += '&page[limit]=1';
    url += '&fields[node--event]=field_event_dates';

    // Initialize the result set.
    let result = '';

    // Make the API request and parse the results.
    try {
      const response = await axios.get(url, {
        headers: {
          'Accept': 'application/vnd.api+json',
          'Content-Type': 'application/vnd.api+json',
        },
      });

      result = response.data.data[0].attributes.field_event_dates.value;
    } catch (error) {
      console.log(error);
    }

    // Return the result set.
    return result;
  }

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
  _normalize(list) {
    // loop through events and convert into smaller, more
    // manageable fields
    const newList = [];
    // map of new keys and their location
    // within the original JSON object
    list.forEach((item) => {
      const newItem = {};
      ({
        id: newItem.id,
        attributes: {
          drupal_internal__nid: newItem.drupalId,
          field_all_day: newItem.allDay,
          field_contact: newItem.contact,
          field_event_dates: {
            value: newItem.start,
            end_value: newItem.end,
          },
          field_event_image_caption: newItem.caption,
          field_event_status: newItem.status,
          field_featured: newItem.featured,
          field_location: newItem.location,
          title: newItem.title,
          path: {
            alias: newItem.pathAlias,
          },
        },
        links: {
          self: {
            href: newItem.link,
          },
        },
      } = item);

      // Since an image is optional, the data is not always present.
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
  _formatImages(images) {
    // create separate array of images from data
    const newList = [];

    if (images) {
      images.forEach((img) => {
        newList[img.id] = img.links.event_image.href;
      });
    }

    return newList;
  }
}

export default EventsAPI;
