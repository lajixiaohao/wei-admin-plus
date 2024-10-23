/**
 * 默认菜单转换为层级树形结构
 *
 * @param data array
 * @param pid number
 * @returns array
 */
export const menuConvert = (data = [], pid = 0) => {
  if (!data) {
    return data;
  }

  let tree = [];

  for (let item of data) {
    if (item.parent_id === pid) {
      item.children = menuConvert(data, item.id);
      tree.push(item);
    }
  }

  return tree;
}

/**
 * 格式化连续年份
 *
 * @param startYear int
 * @return int|string
 */
export const getDurationYear = startYear => {
  const date = new Date();
  const thisYear = date.getFullYear();
  return startYear === thisYear ? startYear : `${startYear} - ${thisYear}`;
}

/**
 * 存储单位转换
 * 
 * @param bytes int
 * @return string
 */
export const byteConvert = (bytes = 0) => {
  const units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  let i = 0;

  while (bytes >= 1024 && i < units.length - 1) {
    bytes /= 1024;
    i++;
  }

  return bytes.toFixed(2) + ' ' + units[i];
}

/**
 * 生成uuid
 */
export const generateUUID = () => {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
    let r = Math.random() * 16 | 0;
    let v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}
