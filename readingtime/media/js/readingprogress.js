/**
 * @author      Carlos M. Cámara
 * @copyright   Copyright (C) 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url         https://extensions.hepta.es
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


const readingProgress = document.querySelector('progress.ert-progress');

document.addEventListener('scroll', function (e) {
    let parent = document.getElementById('ert-start').parentElement;
    let start = parent.offsetTop;
    let end = parent.offsetHeight;
    let w = 0;

    let topScroll = document.documentElement.scrollTop;

    let scrollHeight = document.documentElement.scrollHeight - end;

    if (topScroll > 0) {
        w = (topScroll) / ((start + end) - document.documentElement.clientHeight) * 100;
    }

    readingProgress.style.setProperty('width', w + '%');

    readingProgress.value = w;
});

//Adapted from https://css-tricks.com/reading-position-indicator/

