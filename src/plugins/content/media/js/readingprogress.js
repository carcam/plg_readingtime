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

    let currentPosition = document.documentElement.scrollTop;

    let scrollHeight = window.innerHeight;

    let offset = 0;

    if (start > scrollHeight) {
        offset = Math.floor(start / scrollHeight);
        start = start - (scrollHeight * offset / 3);
    }

    if (currentPosition > start) {
        w = (currentPosition - start) / (end + (scrollHeight * offset / 2) - scrollHeight) * 100;
        console.log(w);
    }

    readingProgress.value = w;
});

//Adapted from https://css-tricks.com/reading-position-indicator/

