var segments = ['Motoring', 'Learners and new drivers', 'Learn to drive'];

module.exports = {
  '@tags': ['nidirect-breadcrumbs'],

  'Test that the driving instructors search page shows correct breadcrumb pattern': browser => {
    browser
      .pause(2000, function () {
        browser.drupalRelativeURL('/services/driving-instructors').waitForElementVisible('body', 2000);
        segments.forEach(function (item, index) {
          browser.expect.element('nav.breadcrumb .breadcrumb--list').text.to.contain(item);
        });
      })
  }

};
