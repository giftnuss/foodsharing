/*

  This is executed by the watch command and is used to decide
  when to rebuild the assets.

  Currently it just watches javascript files, but it should also care
  about css.

*/
module.exports = function(path, stat){

  // ignore anything in a node_modules dir
  if (/node_modules/.test(path)) return false;

  // descend into directories
  if (stat.isDirectory()) return true;

  // reject all none .js files
  if (!/\.js$/.test(path)) return false;

  // this is the output file, will recursive if we do not exclude
  if (path === 'js/gen/script.js') return false;

  return true;
};
