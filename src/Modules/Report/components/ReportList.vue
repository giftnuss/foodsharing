<template>
  <div class="container bootstrap">
    <div class="card mb-3 rounded">
      <div class="card-header text-white bg-primary">
        Alle Betriebe aus dem Bezirk {{ regionName }} (<span v-if="reports.length !== reportsFiltered.length">{{ reportsFiltered.length }} von </span>{{ reports.length }})
      </div>
      <div
        v-if="reports.length"
        class="card-body p-0">

        <b-table
          :fields="fields"
          :items="reportsFiltered"
          :current-page="currentPage"
          :per-page="perPage"
          responsive
        >
        <template slot="avatar" slot-scope="row">
          <Avatar
              :url="row.item.rp_photo"
              :sleep-status="0"
              :size="75"
            />
        </template>

        <template slot="actions" slot-scope="row">
          <b-button size="sm" @click.stop="row.toggleDetails">
            {{ row.detailsShowing ? 'Hide' : 'Show' }} Report
          </b-button>
        </template>
        <template slot="row-details" slot-scope="row">
          <h4>{{row.item.fs_name}} {{ row.item.fs_nachname }}</h4>
          <p>{{row.item.rp_content}}</p>
        </template>
        </b-table>
        <div class="float-right p-1 pr-3">
          <b-pagination
            :total-rows="reportsFiltered.length"
            :per-page="perPage"
            v-model="currentPage"
            class="my-0" />
        </div>
      </div>
      <div
        v-else
        class="card-body">
        Es sind noch keine Betriebe eingetragen
      </div>
      <b-modal id="modalInfo" :title="modalInfo.title" ok-only>
        <pre>{{ modalInfo.content }}</pre>
      </b-modal>
    </div>
  </div>
</template>

<script>
import bTable from '@b/components/table/table'
import bPagination from '@b/components/pagination/pagination'
import bFormSelect from '@b/components/form-select/form-select'
import bModal from '@b/components/modal/modal'
import bButton from '@b/components/button/button'
import bTooltip from '@b/directives/tooltip/tooltip'

import Avatar from '@/components/avatar'

const noLocale = /^[\w-.\s,]*$/

export default {
  components: { Avatar, bTable, bPagination, bFormSelect, bModal, bButton },
  directives: { bTooltip },
  props: {
    regionName: {
      type: String,
      default: ''
    },
    reports: {
      type: Array,
      default: () => [
        {
          "id": "5b796e16dcf523d83786c3c8",
          "fs_id": "5b796e161f271bbbb97fdc53",
          "rp_id": "5b796e16dccf8499ba29d021",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Haley",
          "b_name": "Reilly",
          "fs_name": "Hunt",
          "fs_nachname": "Slater",
          "fs_stadt": "Alden",
          "time_ts": "Mon Aug 29 1988 02:20:23 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1643a95bf2b9f8ccd5",
          "fs_id": "5b796e169ceb82dee998159d",
          "rp_id": "5b796e16911da937747850b4",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Tamara",
          "b_name": "Mckay",
          "fs_name": "Cindy",
          "fs_nachname": "Herrera",
          "fs_stadt": "Dixonville",
          "time_ts": "Tue May 18 2004 06:00:37 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16995407668d806484",
          "fs_id": "5b796e16db6fc269805eb94d",
          "rp_id": "5b796e1650ec48b7310cd805",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Nguyen",
          "b_name": "Warren",
          "fs_name": "Katherine",
          "fs_nachname": "Mullins",
          "fs_stadt": "Grapeview",
          "time_ts": "Wed Jan 31 1973 02:36:05 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e169cacd5f7a067d854",
          "fs_id": "5b796e16c7a9e7f4948a29dc",
          "rp_id": "5b796e1638f660238f3a9622",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Campos",
          "b_name": "Deloris",
          "fs_name": "Stewart",
          "fs_nachname": "Dale",
          "fs_stadt": "Defiance",
          "time_ts": "Sun Dec 04 1977 08:25:04 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16a059b16d754ab614",
          "fs_id": "5b796e16f3fc4ae04050f952",
          "rp_id": "5b796e16a8b157bc97af0e85",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Verna",
          "b_name": "Ramona",
          "fs_name": "Isabel",
          "fs_nachname": "Carlson",
          "fs_stadt": "Fostoria",
          "time_ts": "Sat Jul 28 2018 05:57:26 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e164fb2ed5b3df748ff",
          "fs_id": "5b796e16c352686a94b81737",
          "rp_id": "5b796e1613a721af69c54f93",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Mcconnell",
          "b_name": "Glover",
          "fs_name": "Carlene",
          "fs_nachname": "Mercer",
          "fs_stadt": "Brethren",
          "time_ts": "Thu Dec 17 2015 17:53:39 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e164d5dee2bbb4109e0",
          "fs_id": "5b796e1658cb3ddefbf8d80b",
          "rp_id": "5b796e16f559170137c08f0c",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Morrow",
          "b_name": "Middleton",
          "fs_name": "Deanne",
          "fs_nachname": "Thomas",
          "fs_stadt": "Sperryville",
          "time_ts": "Fri Jan 23 1981 03:41:32 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16cf42291170f683ca",
          "fs_id": "5b796e1645d89f91ba1af101",
          "rp_id": "5b796e16147fbd18a482f749",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Miranda",
          "b_name": "Watkins",
          "fs_name": "Janice",
          "fs_nachname": "Luna",
          "fs_stadt": "Malott",
          "time_ts": "Sat Jul 03 1982 12:46:01 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16a5efabdd49075e5d",
          "fs_id": "5b796e16a86008f30ebc2df6",
          "rp_id": "5b796e16932566de4d2b7391",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Galloway",
          "b_name": "Theresa",
          "fs_name": "Curtis",
          "fs_nachname": "Bruce",
          "fs_stadt": "Coinjock",
          "time_ts": "Wed Oct 14 2009 02:45:54 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1634ac87e44d9dc8a4",
          "fs_id": "5b796e16e88415f57a760a4a",
          "rp_id": "5b796e161fa29c9309aeb768",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Kelley",
          "b_name": "Schwartz",
          "fs_name": "Jeanine",
          "fs_nachname": "Wilson",
          "fs_stadt": "Titanic",
          "time_ts": "Thu Aug 09 1984 07:08:34 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e169f3c5721effba04d",
          "fs_id": "5b796e16c84d553e8b12a179",
          "rp_id": "5b796e16a90350903c3033b3",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Inez",
          "b_name": "Sandoval",
          "fs_name": "Aurelia",
          "fs_nachname": "Silva",
          "fs_stadt": "Hinsdale",
          "time_ts": "Thu Jul 24 2003 11:52:06 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16d1ec83b65c6ecf58",
          "fs_id": "5b796e16598739108975dbe9",
          "rp_id": "5b796e160d7d28916305a12f",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Catherine",
          "b_name": "Briggs",
          "fs_name": "Suarez",
          "fs_nachname": "Randolph",
          "fs_stadt": "Elliston",
          "time_ts": "Wed Sep 15 1982 22:31:01 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16a9f659628db5ce3a",
          "fs_id": "5b796e167adb9896d6f1add4",
          "rp_id": "5b796e161187744a5a639ba1",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Sadie",
          "b_name": "Darcy",
          "fs_name": "Lucinda",
          "fs_nachname": "Cortez",
          "fs_stadt": "Stewart",
          "time_ts": "Fri Dec 02 2011 13:49:28 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e168c7d0988fffae6e0",
          "fs_id": "5b796e1628f1d6c988e966d8",
          "rp_id": "5b796e16c50a6af88680b800",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Todd",
          "b_name": "Pearl",
          "fs_name": "Lee",
          "fs_nachname": "Parsons",
          "fs_stadt": "Nelson",
          "time_ts": "Tue Apr 11 1995 10:09:44 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16899d87c0f2b05ee4",
          "fs_id": "5b796e161e1051eea6e14f76",
          "rp_id": "5b796e1692bb4bcaae1001fc",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Lamb",
          "b_name": "Welch",
          "fs_name": "Baxter",
          "fs_nachname": "Walsh",
          "fs_stadt": "Harmon",
          "time_ts": "Wed Feb 10 1999 16:07:16 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16c9237703a285b100",
          "fs_id": "5b796e16b7a2f479d70044a2",
          "rp_id": "5b796e16dbdea1de9f0b1eaf",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Melisa",
          "b_name": "Herring",
          "fs_name": "Jimenez",
          "fs_nachname": "England",
          "fs_stadt": "Sunwest",
          "time_ts": "Wed Jan 13 2016 01:18:57 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16e49857e0f7c0b32d",
          "fs_id": "5b796e162a939ee016ae97d9",
          "rp_id": "5b796e16bf2574d7b2e4b697",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Byers",
          "b_name": "Mildred",
          "fs_name": "Watson",
          "fs_nachname": "Spence",
          "fs_stadt": "Kylertown",
          "time_ts": "Fri Dec 11 1970 01:14:28 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e16e2085792815de60f",
          "fs_id": "5b796e1685eabc725c6d3823",
          "rp_id": "5b796e17778cc7e799d50ba9",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Williamson",
          "b_name": "Mandy",
          "fs_name": "Burris",
          "fs_nachname": "Ewing",
          "fs_stadt": "Neibert",
          "time_ts": "Mon Dec 04 2017 08:29:04 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e177cae9d80693e5982",
          "fs_id": "5b796e17ca4a9ffcfe80817a",
          "rp_id": "5b796e17c4fa66ea0979aca7",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Tameka",
          "b_name": "Patton",
          "fs_name": "Rogers",
          "fs_nachname": "Rodgers",
          "fs_stadt": "Chemung",
          "time_ts": "Fri Nov 23 1973 04:33:13 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17e351558ffd09f7fd",
          "fs_id": "5b796e1739b7ef13efb3761a",
          "rp_id": "5b796e17153487cdbd0ff2a0",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Frank",
          "b_name": "Barber",
          "fs_name": "Sweeney",
          "fs_nachname": "Robles",
          "fs_stadt": "Movico",
          "time_ts": "Fri Dec 19 1980 06:57:09 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17ceb0c1300d0dd0f5",
          "fs_id": "5b796e1776d0793a122aacba",
          "rp_id": "5b796e179e845321a6a2a920",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Drake",
          "b_name": "Blake",
          "fs_name": "Crane",
          "fs_nachname": "Montgomery",
          "fs_stadt": "Nicholson",
          "time_ts": "Wed Jan 10 1979 10:54:16 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17b4f0203418b38c5e",
          "fs_id": "5b796e17ca4e0aca87e4b512",
          "rp_id": "5b796e17d9ea847dae4cb510",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Sondra",
          "b_name": "Candace",
          "fs_name": "Mcclure",
          "fs_nachname": "Carroll",
          "fs_stadt": "Adelino",
          "time_ts": "Tue Mar 22 1994 07:51:07 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17cae3929ad2363a2c",
          "fs_id": "5b796e17b00d4238b31519f5",
          "rp_id": "5b796e174318ffd8f93e97cc",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Hall",
          "b_name": "Young",
          "fs_name": "Fulton",
          "fs_nachname": "Harris",
          "fs_stadt": "Century",
          "time_ts": "Mon Dec 08 2008 02:19:13 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1775f3c2aa7796d05a",
          "fs_id": "5b796e179cf8091b36dd4426",
          "rp_id": "5b796e17be5c9c610d27fb01",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Dodson",
          "b_name": "Brewer",
          "fs_name": "Sonja",
          "fs_nachname": "Carney",
          "fs_stadt": "Chalfant",
          "time_ts": "Thu Jun 21 1984 15:13:18 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1702a5be846020e022",
          "fs_id": "5b796e17ed52c9a4c4ca3766",
          "rp_id": "5b796e1745e27e9080c319f2",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Oneill",
          "b_name": "Guthrie",
          "fs_name": "Saunders",
          "fs_nachname": "James",
          "fs_stadt": "Bainbridge",
          "time_ts": "Sat Aug 07 1976 02:12:52 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17dd6466b112ebc105",
          "fs_id": "5b796e1794f40f5f38b40b8c",
          "rp_id": "5b796e17b6f4a8557c2156be",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Wooten",
          "b_name": "Karyn",
          "fs_name": "Winnie",
          "fs_nachname": "Whitney",
          "fs_stadt": "Biddle",
          "time_ts": "Mon Nov 28 2005 16:55:34 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17ee9c635cbc9d1677",
          "fs_id": "5b796e17679b6d7fbd9607f4",
          "rp_id": "5b796e1738029e22c18bc8b5",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Smith",
          "b_name": "Augusta",
          "fs_name": "Dawson",
          "fs_nachname": "Parks",
          "fs_stadt": "Crisman",
          "time_ts": "Thu Feb 23 1989 18:06:52 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e177b24e49710810b0b",
          "fs_id": "5b796e172722a123e431e4cc",
          "rp_id": "5b796e1713e9a9015796ac27",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Dixie",
          "b_name": "Shirley",
          "fs_name": "Crawford",
          "fs_nachname": "Cash",
          "fs_stadt": "Crenshaw",
          "time_ts": "Thu Jan 28 1988 15:22:35 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1792cd96fa4e9a0761",
          "fs_id": "5b796e175c8de5c9248d912f",
          "rp_id": "5b796e1752ae142d331c6876",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Tisha",
          "b_name": "Amanda",
          "fs_name": "Bailey",
          "fs_nachname": "Whitfield",
          "fs_stadt": "Glenville",
          "time_ts": "Sat Jun 01 1985 18:55:03 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17ce96cac546a9b9ed",
          "fs_id": "5b796e170dae070cddf382eb",
          "rp_id": "5b796e173ccfc4c754da6de6",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Greene",
          "b_name": "Roberts",
          "fs_name": "Sweet",
          "fs_nachname": "Morales",
          "fs_stadt": "Heil",
          "time_ts": "Fri Mar 21 1975 08:43:04 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17e2242c7fa9c57ae1",
          "fs_id": "5b796e17c3ac7e806316de99",
          "rp_id": "5b796e173d111f88b1b4c851",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Noelle",
          "b_name": "Traci",
          "fs_name": "Ochoa",
          "fs_nachname": "Figueroa",
          "fs_stadt": "Boykin",
          "time_ts": "Sun Nov 07 1982 01:26:36 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1724a5de5f68ad91ba",
          "fs_id": "5b796e1735ab70e4c181921c",
          "rp_id": "5b796e174a679c70c8366eab",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Keisha",
          "b_name": "May",
          "fs_name": "Alisha",
          "fs_nachname": "Foreman",
          "fs_stadt": "Lindcove",
          "time_ts": "Mon Jan 13 1992 08:19:51 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e178dbe9ce9949f104b",
          "fs_id": "5b796e17b82f9d4b33166a44",
          "rp_id": "5b796e178f63b42f18209f3f",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Hooper",
          "b_name": "Glass",
          "fs_name": "Winters",
          "fs_nachname": "Sherman",
          "fs_stadt": "Thomasville",
          "time_ts": "Sat May 04 1974 00:42:06 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17a3a7e449adac50ea",
          "fs_id": "5b796e17c93df853e17f0ef6",
          "rp_id": "5b796e17d1b2672b33e2be7b",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Louisa",
          "b_name": "Karen",
          "fs_name": "Robert",
          "fs_nachname": "Fulton",
          "fs_stadt": "Rote",
          "time_ts": "Sun May 08 2005 08:09:52 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e171fac100c53c08b27",
          "fs_id": "5b796e17f8a0fcce784ce1b0",
          "rp_id": "5b796e1722653cdb77617c9d",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Macias",
          "b_name": "Caitlin",
          "fs_name": "Leta",
          "fs_nachname": "Green",
          "fs_stadt": "Lafferty",
          "time_ts": "Mon Aug 23 1993 05:22:38 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17bebfd07b08b6352c",
          "fs_id": "5b796e174bddb485f3a9d962",
          "rp_id": "5b796e173d16b7f49e476656",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Marietta",
          "b_name": "Austin",
          "fs_name": "Townsend",
          "fs_nachname": "Spencer",
          "fs_stadt": "Tyro",
          "time_ts": "Fri Jan 28 1972 21:04:03 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17e64d24e6b22ada67",
          "fs_id": "5b796e17f88fb6f3b8e1f10c",
          "rp_id": "5b796e174fbe1513ca36bc9c",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Solis",
          "b_name": "Trina",
          "fs_name": "Jacquelyn",
          "fs_nachname": "Davis",
          "fs_stadt": "Turpin",
          "time_ts": "Mon Oct 10 1977 01:45:07 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1770c2ed83818b410c",
          "fs_id": "5b796e17c2eeff658c75f29e",
          "rp_id": "5b796e177cf20088ac2d4685",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Jody",
          "b_name": "Jacobson",
          "fs_name": "Aurora",
          "fs_nachname": "Sears",
          "fs_stadt": "Mammoth",
          "time_ts": "Wed Dec 30 1981 17:06:42 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1781d75a82f07291a2",
          "fs_id": "5b796e173b4ebf5491efa00d",
          "rp_id": "5b796e17701d4eed1fd5fafc",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Angie",
          "b_name": "Shelby",
          "fs_name": "Solomon",
          "fs_nachname": "Parrish",
          "fs_stadt": "Savannah",
          "time_ts": "Sun Aug 30 1987 02:17:46 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17127273ce9f362ff9",
          "fs_id": "5b796e177fd7890b7b8d360d",
          "rp_id": "5b796e173593a052e1f19bc2",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Dorothy",
          "b_name": "Candy",
          "fs_name": "Amalia",
          "fs_nachname": "Hopper",
          "fs_stadt": "Darlington",
          "time_ts": "Wed Jul 08 2015 02:16:51 GMT+0200 (CEST)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e170b72621642a61e05",
          "fs_id": "5b796e172384388415d0c5de",
          "rp_id": "5b796e17897aa6cc461b8e50",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Juanita",
          "b_name": "Barbara",
          "fs_name": "Bradley",
          "fs_nachname": "Gross",
          "fs_stadt": "Hegins",
          "time_ts": "Thu Mar 22 2012 19:01:27 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e1773813245420bede2",
          "fs_id": "5b796e17ddcbb979f66230d5",
          "rp_id": "5b796e17a425ef5d7289acc7",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Nina",
          "b_name": "Cash",
          "fs_name": "Holmes",
          "fs_nachname": "Petty",
          "fs_stadt": "Bradenville",
          "time_ts": "Thu Oct 25 1973 14:22:15 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        },
        {
          "id": "5b796e17d91bfde87bbd027a",
          "fs_id": "5b796e170245532908e3169c",
          "rp_id": "5b796e1787702d759744c7e4",
          "rp_photo": "http://placehold.it/130x130",
          "rp_name": "Alberta",
          "b_name": "Lilly",
          "fs_name": "Melody",
          "fs_nachname": "Summers",
          "fs_stadt": "Biehle",
          "time_ts": "Tue Jan 22 1985 15:46:23 GMT+0100 (CET)",
          "rp_content": " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus eleifend blandit hendrerit. Nullam ullamcorper efficitur lorem, at suscipit nunc ullamcorper et. Proin ac elementum dolor, quis pulvinar massa. Cras egestas finibus porta. Vestibulum lobortis orci nec libero ultricies dignissim. Maecenas pulvinar diam ac dictum aliquam. Duis sed placerat massa. Donec fermentum justo non velit consectetur luctus. Donec vitae felis non elit maximus luctus non eu nulla. "
        }
      ]
    }
  },
  data () {
    return {
      currentPage: 1,
      perPage: 100,
      filterText: '',
      filterStatus: null,
      fields: {
        avatar: {
          labael: 'Avatar'
        },
        fs_stadt: {
          label: 'Stadt',
          sortable: true
        },
        fs_name: {
          label: 'Name',
          sortable: true
        },
        fs_nachname: {
          label: 'Nachname',
          sortable: true
        },
        rp_name: {
          label: 'Report',
          sortable: true
        },
        rp_nachname: {
          label: '',
          sortable: true
        },
        b_name: {
          label: '',
          sortable: true
        },
        actions: {
          label: 'Actions'
        }
      },
      modalInfo: { title: '', content: '' },
      info (item, index, button) {
        this.modalInfo.title = `${item.fs_name} ${item.fs_nachname}`
        this.modalInfo.content = JSON.stringify(item, null, 2)
        this.$root.$emit('bv::show::modal', 'modalInfo', button)
      },

    }
  },
  computed: {
    reportsFiltered: function () {
      if (!this.filterText.trim() && !this.filterStatus) return this.reports
      let filterText = this.filterText ? this.filterText.toLowerCase() : null
      return this.reports.filter((store) => {
        return (
          (!this.filterStatus || store.status === this.filterStatus) &&
          (!filterText || (
            store.name.toLowerCase().indexOf(filterText) !== -1 ||
            store.address.toLowerCase().indexOf(filterText) !== -1 ||
            store.region.toLowerCase().indexOf(filterText) !== -1
          ))
        )
      })
    }
  }
}
</script>
