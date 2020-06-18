# Inquiries component

The Inquiries is intended for the sidebar.

- Heading text
- For each contact section,
  - contact name, title, and department, optional
  - contact links for phone, fax, and/or email
  - icon for phone or email, optional but recommended
  
If there are multiple contact sections, a border will separate them.

## Usage

```html
<div class="c-inquiries">
  <div class="c-inquiries__ctn">
    <h2 class="c-inquiries__heading">Inquiries</h2>
    <div class="c-inquiries__section">
      <div class="c-inquiries__contact">
        <p><strong>Marielle Sainvilus</strong></p>
        <p>Director of Public Affairs</p>
        <p>University Communications</p>
      </div>
      <ul class="c-inquiries__links">
        <li class="c-inquiries__link"><svg class="c-icon">
            <use xlink:href="/themes/custom/uccollege/dist/icons.svg#email-solid"></use>
          </svg> <a href="#">msainvilus@uchicago.edu</a></li>
        <li class="c-inquiries__link"><svg class="c-icon">
            <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phone-solid"></use>
          </svg> <a href="#">(773) 834-9159</a></li>
      </ul>
    </div>
  </div>
</div>
```
