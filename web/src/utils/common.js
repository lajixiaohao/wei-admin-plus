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
 * 字节转换，最大为MB
 * 
 * @param size int
 * @return string
 */
export const byteConvert = (size = 0) => {
  if (size < 1024) {
    return size + 'B';
  }
  const kb = size / 1024;
  const ceilKb = Math.ceil(kb);
  if (ceilKb < 1024) {
    return (Math.floor(kb * 10) / 10) + 'KB';
  }
  const mb = ceilKb / 1024;
  if (Math.ceil(mb) < 1024) {
    return (Math.floor(mb * 10) / 10) + 'MB';
  }
  return num;
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
