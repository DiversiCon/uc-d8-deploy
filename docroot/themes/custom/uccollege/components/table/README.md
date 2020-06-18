# table component

Basic html table for data and info. A responsive table that scrolls horizontally can be created by added the `table-scroll` class. The `c-table` container div is not necessary for table styles, but is required for the scrolling table.

## Usage

```html
<div class="c-table table-scroll">
  <table>
    <caption>Please note the following important dates:</caption>
    <thead>
      <tr>
        <th>Program/<wbr>Session</th>
        <th>Add Deadline</th>
        <th>Drop Deadline</th>
        <th>First day of session</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Session 1: 3-week programs</td>
        <td>June 17 *</td>
        <td>2nd day of class</td>
        <td>June 17, 2019</td>
      </tr>
      <tr>
        <td>Session 1: 5-week programs</td>
        <td>June 17</td>
        <td>4th day of class</td>
        <td>June 17, 2019</td>
      </tr>
      <tr>
        <td>Session 2</td>
        <td>June 8*</td>
        <td>2nd day of class</td>
        <td>July 8, 2019</td>
      </tr>
      <tr>
        <td>Session 3</td>
        <td>July 22</td>
        <td>4th day of class</td>
        <td>July 22, 2019</td>
      </tr>
      <tr>
        <td>Session 4</td>
        <td>July 29</td>
        <td>2nd day of class</td>
        <td>July 29, 2019</td>
      </tr>
      <tr>
        <td>Summer Language Institute</td>
        <td>July 17</td>
        <td>2nd day of class</td>
        <td>June 17, 2019</td>
      </tr>
    </tbody>
  </table>
</div>
```
