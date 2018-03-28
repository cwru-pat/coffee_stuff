$(document).ready(function() {
  const ajaxData = {
      dataType: 'json',
      url: 'js/getdates.php',
    };

  $.ajax(ajaxData).done(function(json) {
    $('#datepick').datepicker({
      format: 'M d',
      clearDates: true,
      todayHighlight: true,
      autoclose: true,
      todayBtn: 'linked',
      keyboardNavigation: false,
      inputs: $('#datepick-start, #datepick-end'),
      beforeShowDay: function(isShownDate) {
        return papersExist(isShownDate, json);
      },
    });

    $('#datepick-short').datepicker({
      format: 'm / d',
      clearDates: true,
      todayHighlight: true,
      autoclose: true,
      todayBtn: 'linked',
      keyboardNavigation: false,
      inputs: $('#datepick-start-short, #datepick-end-short'),
      beforeShowDay: function(isShownDate) {
        return papersExist(isShownDate, json);
      },
    });

    $('#datepick-votes').datepicker({
      clearDates: true,
      todayHighlight: true,
      keyboardNavigation: false,
      inputs: $('#datepick-votes .cal-input'),
      beforeShowDay: function(isShownDate) {
        return papersExist(isShownDate, json);
      },
    });

    $(window).on('scroll', function() {
      $('.cal-input').datepicker('hide');
      $('#datepick-end').blur();
      $('#datepick-start').blur();
      $('#datepick-end-short').blur();
      $('#datepick-start-short').blur();
    });

    let once = false;
    if (!once) {
      $('.date-start').datepicker('setDate', urlToDates()[0]);
      $('.date-end').datepicker('setDate', urlToDates()[1]);
      once = true;
    }

    $('.cal-input').datepicker().on('changeDate', function() {
      if (once) {
        setDateRange($(this).parents('.input-daterange'));
      }
    });
  }).fail(function(jqXHR, textStatus, errorThrown) {
    console.log('Error getting Dates', textStatus, errorThrown, jqXHR);
  });
});

function papersExist(date, availableDates) {
  const dmy = date.getFullYear() + '-' +
    ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
    ('0' + date.getDate()).slice(-2);
  return ($.inArray(dmy, availableDates) != -1) ? true : false;
}

function parse(val) {
  let result = false;
  let tmp = [];
  window.location.search.substr(1).split('&').forEach(function(item) {
    tmp = item.split('=');
    if (tmp[0] === val) {
      result = decodeURIComponent(tmp[1]).replace(/\-/g, '/');
    }
  });
  return result;
}

function urlToDates() {
  let date = [];
  let dstring = parse('ds');
  date[0] = new Date(dstring);
  if (dstring === false) {
    date[0] = new Date();
  }
  dstring = parse('de');
  date[1] = new Date(dstring);
  if (dstring === false) {
    date[1] = date[0];
  }
  return date;
}

function setDateRange(dateGroup) {
  const startDate = dateGroup.find('.date-start').datepicker('getDate');
  const endDate = dateGroup.find('.date-end').datepicker('getDate');
  window.location = window.location.protocol + '//' +
    window.location.host + window.location.pathname +
    '?ds=' + startDate.getFullYear() + '-' + (startDate.getMonth() + 1) +
    '-' + (startDate.getDate()) +
    '&de=' + endDate.getFullYear() + '-' + (endDate.getMonth() + 1) +
    '-' + (endDate.getDate());
}

function setDateRange2(startDate, endDate) {
  window.location = window.location.protocol + '//' +
    window.location.host + window.location.pathname +
    '?ds=' + startDate.getFullYear() + '-' + (startDate.getMonth() + 1) +
    '-' + (startDate.getDate()) +
    '&de=' + endDate.getFullYear() + '-' + (endDate.getMonth() + 1) +
    '-' + (endDate.getDate());
}
