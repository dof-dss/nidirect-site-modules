var segments = ['Education', 'Schools, learning and development', 'School life'];

module.exports = {
  '@tags': ['nidirect-breadcrumbs'],

  'Test that the School closures page shows correct breadcrumb pattern': browser => {
    browser
      .pause(2000, function () {
        browser.drupalRelativeURL('/services/school-closures').waitForElementVisible('body', 2000);
        segments.forEach(function (item, index) {
          browser.expect.element('nav.breadcrumb .breadcrumb--list').text.to.contain(item);
        });
      })
  }

};
