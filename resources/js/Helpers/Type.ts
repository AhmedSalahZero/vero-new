 export interface IdTitleInterface<T, K> {
  id: T
  title: K
} 

export interface numericObject {
  [key: string]: number
}
export interface datesInterface {
  [key: string]: string
}
export interface objectAsStringAndNumberInterface {
  [key: string]: number
}
export interface objectAsStringAndNumberOrStringInterface {
  [key: string]: number|string
}
export interface objectAsStringAndStringInterface {
  [key: string]: string
}
