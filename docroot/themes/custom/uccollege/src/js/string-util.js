/**
 * String related utility functions.
 */
class StringUtil {
  /**
   * Performs a series of functions to clean the given string of unwanted characters.
   *
   * @param {string} value
   *   String to clean.
   *
   * @returns {string}
   */
  static cleanString(value) {
    value = this.unescapeDoubleQuotes(value);
    value = this.unescapeSingleQuotes(value);

    return value;
  }

  /**
   * Unescapes single quotes.
   *
   * @param {string} value
   *
   * @returns {string}
   */
  static unescapeSingleQuotes(value) {
    return value.replace(/\\'/g, "'");
  }

  /**
   * Unescapes double quotes.
   *
   * @param {string} value
   *
   * @returns {string}
   */
  static unescapeDoubleQuotes(value) {
    return value.replace(/\\"/g, '"');
  }
}

export default StringUtil;
