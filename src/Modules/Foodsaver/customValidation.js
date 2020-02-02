import differenceInCalendarYears from 'date-fns/difference_in_calendar_years'
import isValid from 'date-fns/is_valid'

export function ageCheck (value) {
  const age = differenceInCalendarYears(new Date(), new Date(value))
  return age >= 18 && age < 125
}

export function dateValid (value) {
  return isValid(new Date(value))
}
