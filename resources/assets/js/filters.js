/**
 * Format the given date.
 */
Vue.filter('date', value => {
    return moment.utc(value).local().format('MMMM Do, YYYY')
});


/**
 * Format the given date as a timestamp.
 */
Vue.filter('datetime', value => {
    return moment.utc(value).local().format('MMMM Do, YYYY h:mm A');
});


/**
 * Format the given date into a relative time.
 */
Vue.filter('relative', value => {
    return moment.utc(value).local().locale('en-short').fromNow();
});


/**
 * Convert the first character to upper case.
 *
 * Source: https://github.com/vuejs/vue/blob/1.0/src/filters/index.js#L37
 */
Vue.filter('capitalize', value => {
    if (! value && value !== 0) {
        return '';
    }

    return value.toString().charAt(0).toUpperCase()
        + value.slice(1);
});


/**
 * Format the given money value.
 *
 * Source: https://github.com/vuejs/vue/blob/1.0/src/filters/index.js#L70
 */
Vue.filter('currency', value => {
    value = parseFloat(value);

    if (! isFinite(value) || (! value && value !== 0)){
        return '';
    }

    let stringified = Math.abs(value).toFixed(2);

    let _int = stringified.slice(0, -1 - 2);

    let i = _int.length % 3;

    let head = i > 0
        ? (_int.slice(0, i) + (_int.length > 3 ? ',' : ''))
        : '';

    let _float = stringified.slice(-1 - 2);

    let sign = value < 0 ? '-' : '';

    return sign + window.Spark.currencySymbol + head +
        _int.slice(i).replace(/(\d{3})(?=\d)/g, '$1,') +
        _float;
});
