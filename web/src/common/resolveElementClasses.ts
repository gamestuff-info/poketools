type ElementClassList = string | Array<string>;

/**
 * Create an element class list
 *
 * @param userClasses User-supplied classes
 * @param forceClasses Required classes
 */
export default function resolveElementClasses(userClasses?: ElementClassList, forceClasses: ElementClassList = []) {
    return getClassList(forceClasses).concat(getClassList(userClasses ?? [])).join(' ');
}

function getClassList(classes: ElementClassList): Array<string> {
    if (Array.isArray(classes)) {
        return classes;
    }
    return classes.split(' ');
}
