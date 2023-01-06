export function useGetLocalXPath(path) {
    let pathArray;
    let returnPath = "";

    path = path.substr(1);
    pathArray = path.split("/");
    for (let j = 0; j < pathArray.length; j++) {
        let splitArr = pathArray[j].split("[");

        if (splitArr[0] == "text()") {
            //add special cases
            returnPath += "/" + splitArr[0] + "[" + splitArr[1];
        } else if (splitArr[1] == null) {//last element not specified -> path to group
            returnPath += "/*[local-name()='" + splitArr[0] + "']";
        } else returnPath += "/*[local-name()='" + splitArr[0] + "'][" + splitArr[1];
    }
    //returnPath += '/*:' + pathArray[pathArray.length-1];
    return returnPath;
}

export function getNode(doc, path) {
    return doc.evaluate(path, doc, null, XPathResult.ANY_TYPE, null).iterateNext()
}