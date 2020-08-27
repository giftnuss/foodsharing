<template>
  <div class="bootstrap">
    <div class="font-weight-bold my-1 mb-2">
      {{ $i18n('poll.results.number_of_votes') }}: {{ numVotes }}
    </div>

    <b-table
      v-if="numValues===1"
      :fields="tableFields1"
      :items="options"
      :sort-compare="compare"
      small
      hover
      responsive
      striped
    >
      <template v-slot:cell(optionText)="row">
        <div>
          {{ row.item.text }}
        </div>
      </template>
      <template v-slot:cell(value)="row">
        {{ row.item.values[1] }}
      </template>
    </b-table>

    <b-table
      v-else-if="numValues===3"
      :fields="tableFields3"
      :items="options"
      :sort-compare="compare"
      small
      hover
      responsive
      striped
    >
      <template v-slot:cell(optionText)="row">
        <div>
          {{ row.item.text }}
        </div>
      </template>
      <template v-slot:cell(value1)="row">
        {{ row.item.values[1] }}
      </template>
      <template v-slot:head(value1)>
        <i class="fas fa-thumbs-up" />
      </template>
      <template v-slot:cell(value0)="row">
        {{ row.item.values[0] }}
      </template>
      <template v-slot:head(value0)>
        <i class="fas fa-meh" />
      </template>
      <template v-slot:cell(value-1)="row">
        {{ row.item.values[-1] }}
      </template>
      <template v-slot:head(value-1)>
        <i class="fas fa-thumbs-down" />
      </template>
      <template v-slot:cell(average)="row">
        <div>
          {{ averageVotes(row.item) }}
        </div>
      </template>
      <template v-slot:head(average)="row">
        {{ row.label }} (<i class="fas fa-thumbs-up" /> - <i class="fas fa-thumbs-down" />)
      </template>
    </b-table>

    <b-table
      v-else-if="numValues===7"
      :fields="tableFields7"
      :items="options"
      :sort-compare="compare"
      small
      hover
      responsive
      striped
    >
      <template v-slot:cell(optionText)="row">
        <div>
          {{ row.item.text }}
        </div>
      </template>
      <template v-slot:cell(value3)="row">
        {{ row.item.values[3] }}
      </template>
      <template v-slot:cell(value2)="row">
        {{ row.item.values[2] }}
      </template>
      <template v-slot:cell(value1)="row">
        {{ row.item.values[1] }}
      </template>
      <template v-slot:cell(value0)="row">
        {{ row.item.values[0] }}
      </template>
      <template v-slot:cell(value-1)="row">
        {{ row.item.values[-1] }}
      </template>
      <template v-slot:cell(value-2)="row">
        {{ row.item.values[-2] }}
      </template>
      <template v-slot:cell(value-3)="row">
        {{ row.item.values[-3] }}
      </template>
      <template v-slot:cell(average)="row">
        <div>
          {{ averageVotes(row.item) }}
        </div>
      </template>
    </b-table>
  </div>
</template>

<script>

import { BTable } from 'bootstrap-vue'
import { optimizedCompare } from '@/utils'

export default {
  components: { BTable },
  props: {
    options: {
      type: Array,
      required: true
    },
    numValues: {
      type: Number,
      required: true
    },
    numVotes: {
      type: Number,
      required: true
    }
  },
  computed: {
    tableFields1 () {
      return [
        {
          key: 'optionText',
          sortable: false,
          label: this.$i18n('poll.results.option_text'),
          class: 'align-left'
        }, {
          key: 'value',
          sortable: true,
          label: this.$i18n('poll.results.votes'),
          class: 'align-middle'
        }
      ]
    },
    tableFields3 () {
      return [
        {
          key: 'optionText',
          sortable: false,
          label: this.$i18n('poll.results.option_text'),
          class: 'align-left'
        }, {
          key: 'value1',
          sortable: true,
          class: 'align-middle'
        }, {
          key: 'value0',
          sortable: true,
          class: 'align-middle'
        }, {
          key: 'value-1',
          sortable: true,
          class: 'align-middle'
        }, {
          key: 'average',
          label: this.$i18n('poll.results.average'),
          sortable: true,
          class: 'align-middle'
        }
      ]
    },
    tableFields7 () {
      const result = []
      result.push({
        key: 'optionText',
        sortable: false,
        label: this.$i18n('poll.results.option_text'),
        class: 'align-left'
      })
      const sortedValues = Object.keys(this.options[0].values).sort(function (a, b) { return b - a })
      for (const key in sortedValues) {
        result.push({
          key: 'value' + sortedValues[key],
          label: sortedValues[key],
          sortable: true,
          class: 'align-middle'
        })
      }
      result.push({
        key: 'average',
        label: this.$i18n('poll.results.average'),
        sortable: true,
        class: 'align-middle'
      })

      return result
    }
  },
  methods: {
    compare: optimizedCompare,
    averageVotes (option) {
      let average = 0
      for (const value in option.values) {
        average += value * option.values[value]
      }
      return average
    }
  }
}
</script>
