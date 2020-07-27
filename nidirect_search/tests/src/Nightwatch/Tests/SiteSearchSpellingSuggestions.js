module.exports = {
  '@tags': ['search'],

  'Spelling suggestions for site search': browser => {

    browser
      .drupalRelativeURL('/search?query=covod')
      .expect.element('.sapi-did-you-mean')
      .text.to.contain('Did you mean: covid');

    browser
      .drupalRelativeURL('/search?query=benfit')
      .expect.element('.sapi-did-you-mean')
      .text.to.contain('Did you mean: benefit');

    browser
      .drupalRelativeURL('/search?query=medcal')
      .expect.element('.sapi-did-you-mean')
      .text.to.contain('Did you mean: medical');

    browser
      .drupalRelativeURL('/search?query=belfst')
      .expect.element('.sapi-did-you-mean')
      .text.to.contain('Did you mean: belfast');

  }

};
