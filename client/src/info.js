/* eslint-disable eqeqeq */

const info = {

  /*
   * an array of the services that the heartbeat have to call
   */
  services: [],

  /*
   * count for heartbeat times
   */
  hbCount: 0,

  hbXhr: null,

  /**
   * Method to add an polling service
   * options are send as GET Parameter to the module action
   *
   * the there are 3 polling speed options {speed:slow|moderate|fast}
   * default is slow = every 10 seconds
   *     moderate is slow/4  => 2.5 seconds as default
   *     fast is slow/20   => 0.5 seconds as default
   *
   * option {premethod:[methodName]} with this option you can define an method which is called before the session is locked for writing
   */
  addService: function (app, method, options) {
    this.services.push({
      a: app,
      m: method,
      o: options
    })

    this.restart()
  },

  /**
   * remove an polling service
   */
  removeService: function (app, method) {
    var tmp = []
    for (var i = 0; i < info.services.length; i++) {
      if (!(info.services[i].a == app || info.services.m == method)) {
        tmp.push(info.services[i])
      }
    }
    this.services = tmp
    this.restart()
  },

  /**
   * modify service parameter
   */
  editService: function (app, method, options) {
    var tmp = []
    for (var i = 0; i < info.services.length; i++) {
      if (!(info.services[i].a == app || info.services.m == method)) {
        tmp.push(info.services[i])
      }
    }

    /**
     * if the service is not in the list just add it
     */
    tmp.push({
      a: app,
      m: method,
      o: options
    })

    info.services = tmp
    this.restart()
  },

  /**
   * restart the heartbead
   */
  restart: function () {
    if (this.hbXhr !== null) {
      info.hbCount = 0
      this.hbXhr.abort()
    }
  }

}

export default info
