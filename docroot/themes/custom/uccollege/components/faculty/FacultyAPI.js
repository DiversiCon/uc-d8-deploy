import axios from 'axios';

class FacultyAPI {
  /**
   * Constructor.
   *
   * @param {object} properties
   *   Properties to initialize with.
   */
  constructor(properties = { endpoint: 'http://bsd-data.dev.uchicago.edu/api/node/faculty' }) {
    this.endpoint = properties.endpoint;
  }

  /**
   * Get faculty member data by given id.
   *
   * @param facultyIds
   *   UUIDs of faculty members to look up.
   *
   * @returns {Promise<Array>}
   */
  async getFacultyById(facultyIds) {
    const fieldsToInclude = 'field_first_name,'
      + 'field_last_name,'
      + 'field_faculty_image,'
      + 'field_show_photo,'
      + 'field_websites,'
      + 'field_administrative_title,'
      + 'field_academic_appointment,'
      + 'path';

    const relationshipFields = 'field_faculty_image,'
      + 'field_websites,'
      + 'field_academic_appointment';

    // Build the request url.
    let url = this.endpoint;

    url += '?include=' + relationshipFields;
    url += '&fields[node--faculty]=' + fieldsToInclude;
    url += '&filter[id-group][group][conjunction]=OR';

    for (let i = 0; i < facultyIds.length; i++) {
      const facultyId = facultyIds[i];
      url += `&filter[event-id-filter-${i}][condition][path]=field_external_id`;
      url += `&filter[event-id-filter-${i}][condition][value]=${facultyId}`;
      url += `&filter[event-id-filter-${i}][condition][memberOf]=id-group`;
    }

    // Initialize the result set.
    let resultSet = [];

    // Make the API request and parse the results.
    try {
      const response = await axios.get(url);

      resultSet = this._normalize(response.data);
    } catch (error) {
      console.log(error);
    }

    return resultSet;
  }

  /**
   * Normalizes the data structure from the JSON API.
   *
   * @param {Array} data
   *   Raw data structure.
   *
   * @returns {Array}
   *
   * @private
   */
  _normalize(data) {
    const normalizedData = [];
    const includedData = data.included;

    data.data.forEach((item) => {
      const normalizedItem = {};

      // Normalize the data that easily accessible.
      (
        {
          id: normalizedItem.id,
          attributes: {
            field_first_name: normalizedItem.firstName,
            field_last_name: normalizedItem.lastName,
            field_administrative_title: normalizedItem.administrativeTitle,
            field_show_photo: normalizedItem.showPhoto,
          },
        } = item
      );

      // Normalize an associated faculty image.
      if (item.relationships.field_faculty_image !== null && item.relationships.field_faculty_image.data != null) {
        const facultyImageId = item.relationships.field_faculty_image.data.id;

        // Initialize the image object.
        normalizedItem.image = {};

        // Search included data for a match to the image id.
        includedData.forEach((includedItem) => {
          // If there is image data, set it in the normalized data structure.
          if (includedItem.id === facultyImageId) {
            if (includedItem.links.faculty_image) {
              normalizedItem.image.original = includedItem.attributes.uri.url;
              normalizedItem.image.resized = includedItem.links.faculty_image.href;
            }
          }
        });
      }

      // Normalize academic appointments. Retrieve the ids of included data first.
      const academicAppointmentIds = [];
      if (item.relationships.field_academic_appointment !== null) {
        item.relationships.field_academic_appointment.data.forEach((appointmentData) => {
          academicAppointmentIds.push(appointmentData.id);
        });
      }

      // Initialize the academic appointments.
      normalizedItem.academicAppointments = [];

      // Loop through any ids, and add them to the normalized data.
      academicAppointmentIds.forEach((id) => {
        includedData.forEach((includedItem) => {
          if (id === includedItem.id) {
            normalizedItem.academicAppointments.push({
              title: includedItem.attributes.field_title,
              department: includedItem.attributes.field_department_name,
            });
          }
        });
      });

      // Normalize any associated websites.
      const websiteIds = [];
      if (item.relationships.field_websites !== null) {
        item.relationships.field_websites.data.forEach((websiteData) => {
          websiteIds.push(websiteData.id);
        });
      }

      // Initialize the website data.
      normalizedItem.websites = [];

      websiteIds.forEach((id) => {
        includedData.forEach((includedItem) => {
          if (id === includedItem.id) {
            normalizedItem.websites.push({
              name: includedItem.attributes.field_website_name,
              url: includedItem.attributes.field_website_url,
            });
          }
        });
      });

      let urlAlias = '/faculty/' + item.id;
      if (item.attributes.path != null) {
        urlAlias = '/faculty' + item.attributes.path.alias;
      }

      normalizedItem.urlAlias = urlAlias;

      // Add the normalized item.
      normalizedData.push(normalizedItem);
    });

    return normalizedData;
  }
}

export default FacultyAPI;
