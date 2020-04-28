import differenceInCalendarYears from 'date-fns/differenceInCalendarYears'
import isValid from 'date-fns/isValid'

export function ageCheck (value) {
  const age = differenceInCalendarYears(new Date(), new Date(value))
  return age >= 18 && age < 125
}

export function dateValid (value) {
  return isValid(new Date(value))
}
