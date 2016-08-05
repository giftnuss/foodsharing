/*

  This is executed by the watch command and is used to decide
  when to rebuild the assets.

*/
module.exports = function(path, stat){

  // ignore anything in a node_modules and vendor dirs
  if (/(vendor|node_modules)/.test(path)) return false;

  // descend into directories
  if (stat.isDirectory()) return true;

  // only include .js or .css files
  if (!/\.(js|css)$/.test(path)) return false;

  // do not include generated contents or we get stuck in a cycle
  if (/\/gen\//.test(path)) return false;

  return true;
};
