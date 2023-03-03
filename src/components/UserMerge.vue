<template>
<div id="user-merge">
    <div id="generalInfo">
        <h3>Semi-automatic Merging</h3>
        <h4>Still under development</h4>
        <p>
            You are in the semi-automatic merging mode. This means you can
            cherry-pick the changes you want to apply. We provide different views on
            the changes:
        </p>
    </div>

    <div class="container">
        <local-files @file1="(f) => this.file1 = f" @file2="(f) => this.file2 = f"></local-files>
    </div>

    <div class="btn-group d-flex" role="group" id="all-btngrp" aria-label="Toggle group">
        <input type="radio" class="btn-check" name="allV1" id="allV1" autocomplete="off" @click="this.allFrom(0)" :checked="all == 0" />
        <label class="btn btn-outline-primary" for="allV1">V1</label>

        <input type="radio" class="btn-check" name="allV2" id="allV2" autocomplete="off" @click="this.allFrom(1)" :checked="all == 1" />
        <label class="btn btn-outline-primary" for="allV2">V2</label>
    </div>

    <div class="progress">
        <div class="progress-bar" role="progressbar" :style="{width: this.progress + '%'}" :aria-valuenow="this.progress" aria-valuemin="0" aria-valuemax="100"> {{ progress }}%</div>
    </div>

    <div class="btn-group d-flex">
        <input type="radio" class="btn-check col-2" name="options" id="modalTab" autocomplete="off">
        <label @click="view = 'model'" class="btn btn-outline-primary col-1" for="modalTab">Model</label>

        <input type="radio" class="btn-check col-2" name="options" id="unitsTab" autocomplete="off" checked>
        <label @click="view = 'units'" class="btn btn-outline-primary col-1" for="unitsTab">Units</label>

        <input type="radio" class="btn-check col-2" name="options" id="parametersTab" autocomplete="off">
        <label @click="view = 'parameters'" class="btn btn-outline-primary col-1" for="parametersTab">Parameters</label>

        <input type="radio" class="btn-check col-2" name="options" id="rulesTab" autocomplete="off">
        <label @click="view = 'rules'" class="btn btn-outline-primary col-1" for="rulesTab">Rules</label>

        <input type="radio" class="btn-check col-2" name="options" id="compartmentsTab" autocomplete="off">
        <label @click="view = 'compartments';" class="btn btn-outline-primary col-1" for="compartmentsTab">Compartments</label>

        <input type="radio" class="btn-check col-2" name="options" id="speciesTab" autocomplete="off">
        <label @click="view = 'species';" class="btn btn-outline-primary col-1" for="speciesTab">Species</label>

        <input type="radio" class="btn-check col-2" name="options" id="reactionsTab" autocomplete="off">
        <label @click="view = 'reactions'" class="btn btn-outline-primary col-1" for="reactionsTab">Reactions</label>

    </div>

    <div v-if="view == 'model'">
        <ul class="list-group">
            <template v-for="(element, index) in modelArr" :key="index">
                <li v-if="index === 'modelAttr' || index === 'sbmlAttr'" class="list-group-item" :id="`${element.id}`">
                    <h5>{{ element.id }}</h5>
                    <ul class="list-group">
                        <template v-for="(el, key) in element.attr" :key="key">
                            <li class="list-group-item d-flex justify-content-evenly row">
                                <div class="col-3"><b>{{ key }}</b>:</div>
                                <div v-if="el.oldValue" class="col-3 delete-color">{{ el.oldValue }}</div>
                                <div v-if="el.newValue" class="col-3 insert-color">{{ el.newValue }}</div>
                                <div v-if="!el.changeID" class="col-9">{{ el }} </div>

                                <div v-if="el.changeID" class="container col-1">
                                    <decision-btn :changeID="el.changeID" :d="this.decisionArr[el.changeID]['decision']" @decision="this.updateDecision" />
                                </div>
                            </li>
                        </template>
                    </ul>
                </li>
            </template>
        </ul>
    </div>

    <div v-else-if="view == 'parameters'">
        <ul>
            <li v-for="(el, index) in modelArr['listOfParameters']" :key="index" class="list-group-item">
                <div class="container">
                    <lists-template :el="el" :decisionArr="this.decisionArr" @decision="this.updateDecision" />
                </div>
            </li>
        </ul>
    </div>

    <div v-else-if="view == 'units'">
        <ul>
            <li v-for="(el, index) in modelArr['listOfUnitDefinitions']" :key="index" class="list-group-item">
                <div class="container">
                    <lists-template :el="el" :decisionArr="this.decisionArr" @decision="this.updateDecision" />
                </div>
            </li>
        </ul>
    </div>

    <div v-else-if="view == 'rules'">
        <ul>
            <li v-for="(el, index) in modelArr['listOfRules']" :key="index" class="list-group-item">
                <div class="container">
                    <lists-template :el="el" :decisionArr="this.decisionArr" @decision="this.updateDecision" />
                </div>
            </li>
        </ul>
    </div>

    <div v-else-if="view == 'compartments'">
        <ul>
            <li v-for="(el, index) in modelArr['listOfCompartments']" :key="index" class="list-group-item">
                <div class="container">
                    <lists-template :el="el" :decisionArr="this.decisionArr" @decision="this.updateDecision" />
                </div>
            </li>
        </ul>
    </div>

    <div v-else-if="view == 'species'" class="container row slider">
        <slider :data="this.speciesArr" :structuredData="this.structuredData" :decisionArr="this.decisionArr" :slideNumber="this.currentSpecies" @decision="this.updateDecision" @slideNum="(num) => this.currentSpecies = num"></slider>
    </div>

    <div v-else-if="view == 'reactions'" class="container row slider">
        <slider :data="this.reactionsArr" :structuredData="this.structuredData" :decisionArr="this.decisionArr" :slideNumber="this.currentReaction" @decision="this.updateDecision" @slideNum="(num) => this.currentReaction = num"></slider>
    </div>

</div>
<div id="devOutput" v-if="dev == 1">
    <p> {{ this.decisionArrCount }}</p>
    <p> {{ Object.keys(this.decisionArr).length}}</p>
    <h3>Dev mode is active!!</h3>
    <p> The Merge is produced with local files for versions and diff.</p>

</div>
<div v-else-if="dev == 2">
    <h3>Dev mode 2</h3>
    <p> The Merge is gitproduced with files from the from.</p>
</div>
<merger :decision-arr="decisionArr" :oldDoc="this.oldDocument" :newDoc="this.newDocument" :xmlDiff="this.xmlDiff" :progress="this.progress"></merger>
</template>

<script>
import Slider from "./Slider.vue";
import DecisionBtn from "./DecisionBtn.vue";
import ListsTemplate from "./ListsTemplate.vue";
import Merger from "./Merger.vue";
import LocalFiles from "./LocalFiles.vue";
import axios from 'axios';
import * as divilApi from "../../DiVil/javascriptAndCss/init";
//import * as mathJax from "../../3rdPartyJS/MathJax-2.7.7/MathJax";

import {
    useGetLocalXPath,
    getNode
} from "../composables/xmlInteraction";

export default {
    name: "user-merge",
    components: {
        Merger,
        ListsTemplate,
        DecisionBtn,
        Slider,
        LocalFiles
    },
    data() {
        return {
            json: null,
            view: "units",
            v1: null,
            v2: null,
            file1: null,
            file2: null,
            hide: true,
            modelArr: [],
            unitsArr: [],
            listsArr: [],
            decisionArr: {}, //testArr
            decisionArrCount: 0,
            all: -1,
            progress: 0,
            progressCount: 0,
            reactionsArr: [],
            speciesArr: [],
            structuredData: null,
            currentSpecies: 0,
            currentReaction: 0,
            oldDocument: null,
            newDocument: null, //should also be given to subcomponent to avoid parsing it twice!
            xmlDiff: null,
            dev: 1, //flag for development, 1: local files, 2: form files
        };
    },
    methods: {
        async checkForm() {
            if (this.file1 != null && this.file2 != null) {
                //get all files
                //const promiseV1 = await axios.get(this.file1);
                //const promiseV2 = await axios.get(this.file2);
                //const promiseDiff = await axios.get('/dev/fake-dupreez/supershort/xmlDiff.xml');
                //const promiseJson = await axios.get('/dev/fake-dupreez/supershort/xmlDiff.xml');

                //////////////
                //const axios = require("axios");
                /*
                    Initialize the form data
                    */
                let formData = new FormData();

                /*
                    Iteate over any file sent over appending the files
                    to the form data.
                    */
                let file = this.file1;
                formData.append("file1", file);
                file = this.file2;
                formData.append("file2", file);
                let bivesJob = "reactionsSbgnJson,xmlDiff";
                //bivesJob = JSON.stringify(bivesJob);
                //for(let i=0; i < bivesJob.length; i++){

                formData.append("commands", bivesJob);
                //}

                /*
                    Make the request to the POST /multiple-files URL
                */

                console.debug(formData);
                const bivesData = await axios
                    .post("/bives/userMerge.php", formData, {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    })
                //////////

                Promise.allSettled([bivesData])
                    .then((responses) => {

                        console.debug("responses:", responses);
                        this.json = JSON.parse(responses[0].value.data.reactionsSbgnJson);
                        this.xmlDiff = responses[0].value.data.xmlDiff;

                        this.v1 = this.file1;
                        this.v2 = this.file2;

                        //this.newDocument = this.file2;
                        let parser1 = new DOMParser();
                        this.newDocument = parser1.parseFromString(this.v2, "text/xml");
                        
                        let parser2 = new DOMParser();
                        this.oldDocument = parser2.parseFromString(this.v1, "text/xml");
                        

                        console.debug("check");
                        this.createInterface();

                    })
                    .catch(error => {
                        console.info(error);
                        console.error(error.message);
                    });


                
            }
        },
        createInterface: function () {
            //compute reaction view
            /*
             * To go through each single change might be cumbersum.
             * With this view we split the network into each reaction.
             * Reactions with changes can than be viewed.
             */

             console.debug("check createInterface");
             console.debug("just a test for git submodule behaivor");

             console.info(this.json, this.oldDocument, this.newDocument, this.xmlDiff);

            this.json.nodes.forEach((node) => {

                if (node.id.startsWith("r") && node.bivesChange != "nothing") {
                    //every reaction ID starts with r
                    let reactionNodes = [node];
                    let reactionLinks = [];
                    //add reaction to Array, include all link and participants
                    this.json.links.forEach((link) => {
                        if (link.target == node.id || link.source == node.id) {
                            reactionLinks.push(link);
                            //add other participant of link
                            let addNode, addNodeId;
                            if (link.target == node.id) {
                                addNodeId = link.source;
                            } else addNodeId = link.target;

                            //console.log(addNodeId, addNode);
                            if (!reactionNodes.some(rN => rN.id === addNodeId)) {
                                //console.info(reactionNodes, addNodeId);
                                //alert("check nodes");
                                addNode = this.json.nodes.find((n) => n.id == addNodeId);
                                reactionNodes.push(addNode);
                            }
                        }
                    });
                    this.reactionsArr.push({
                        centralNode: node.path,
                        bivesChange: node.bivesChange,
                        nodes: reactionNodes,
                        links: reactionLinks,
                    });
                } else if (node.id.startsWith("s") && node.bivesChange != "nothing") { //want the subnetwork for the distance 2
                    let speciesNodes = [node];
                    let speciesLinks = [];
                    //add reaction to Array, include all link and participants
                    this.json.links.forEach((link) => {
                        if (link.target == node.id || link.source == node.id) {
                            speciesLinks.push(link);
                            //add other participant of link
                            let addNode, addNodeId;
                            if (link.target == node.id) {
                                addNodeId = link.source;
                            } else addNodeId = link.target;

                            if (!speciesNodes.some(sN => sN.id === addNodeId)) {
                                addNode = this.json.nodes.find((n) => n.id == addNodeId);
                                speciesNodes.push(addNode);
                            }
                            this.json.links.forEach((link2) => {

                                if (link2.target == addNodeId || link2.source == addNodeId) {

                                    //alert("before before");

                                    if (!speciesLinks.some(sL => sL.path === link2.path)) {
                                        speciesLinks.push(link2);

                                        let addNode2, addNodeId2;
                                        if (link2.target == addNodeId) addNodeId2 = link2.source;
                                        else addNodeId2 = link2.target;

                                        if (!speciesNodes.some(sN => sN.id === addNodeId2)) {
                                            //alert("check nodes"); 
                                            addNode2 = this.json.nodes.find((n) => n.id === addNodeId2);
                                            speciesNodes.push(addNode2);
                                        }
                                    }
                                }
                            })
                        }
                    });

                    console.debug(node);
                    //alert("before push");
                    this.speciesArr.push({
                        centralNode: node.path,
                        bivesChange: node.bivesChange,
                        nodes: speciesNodes,
                        links: speciesLinks,
                    });
                }
            });

            console.info(this.xmlDiff);
            //init computed divil data
            this.structuredData = divilApi.initDivil(this.xmlDiff, this.v1, this.v2);

            // get units
            console.debug(this.decisionArr);

            console.debug("combine model attr with changes");
            this.combineModelAttrWithChanges();
        },

        createInterfaceLocally: function (promiseV1, promiseV2, promiseDiff, promiseJson) {
            Promise.allSettled([promiseV1, promiseV2, promiseDiff, promiseJson])
                .then((responses) => {
                    console.log(responses);
                    this.v1 = responses[0].value.data;
                    this.v2 = responses[1].value.data;
                    this.xmlDiff = responses[2].value.data;
                    let parser1 = new DOMParser();
                    this.newDocument = parser1.parseFromString(this.v2, "text/xml");
                    let parser2 = new DOMParser();
                    this.oldDocument = parser2.parseFromString(this.v1, "text/xml");

                    console.debug(responses);

                    this.json = responses[3].value.data;
                    console.debug(this.json);

                    this.createInterface();

                })
                .catch(error => {
                    console.info(error);
                    console.error(error.message);
                });

            console.log("Dev Mode is active!");
            console.log("reactionsArr: ", this.reactionsArr);

           
        },
        filterChangeAttr: function (el, attr) {
            console.info(el, attr);
            alert("wqdesasd");
        },
        getLineAttr: function (line, attr) {
            let regex = new RegExp(attr + '="(.*?)"', 'g');
            return regex.exec(line)[1];
        },
        getModelChanges: function () {
            let modelChanges = [];
            let xmlLines = this.xmlDiff.split(/\r?\n/);
            let type;
            let parser = new DOMParser();
            let speciesAndReactionMath = {};
            xmlLines.forEach(line => {
                if (line.includes("triggeredBy="));
                else if (line.includes("<move>")) type = null;
                else if (line.includes("<insert>")) {
                    type = 'i';
                } else if (line.includes("<delete>")) {
                    type = 'd';
                } else if (line.includes("<update>")) {
                    type = 'u';
                } else if ((line.includes('/sbml[1]\"') || line.includes('/sbml[1]/model[1]\"')) && type != null) {

                    line = parser.parseFromString(line, "application/xml");

                    let p = {};
                    p["type"] = type;
                    p["target"] = line.firstChild.localName;

                    let attr = line.firstChild.attributes;

                    for (let i = 0; i < attr.length; i++) {
                        p[attr[i].localName] = attr[i].value;
                    }

                    modelChanges.push(p);
                } else if (type != null && (line.includes("/listOfSpecies") || line.includes("/listOfReactions"))) { //species and reaction changes are shown with DiVil, but we still need to add the changes to the decision array

                    let id = this.getLineAttr(line, 'id');
                    /*                    let path;
                                       if(type == 'u' || type == 'i') path = this.getLineAttr(line, 'newPath');
                                       else path = this.getLineAttr(line, 'oldPath');

                                       if(path.includes('math')){
                                           path = path.substring(0, path.indexOf("/math"));
                                           console.debug(speciesAndReactionMath);
                                           if(speciesAndReactionMath[path + TYPE]) return;
                                       } */

                    this.decisionArr[id] = {
                        "type": type,
                        "decision": -1
                    };
                    this.decisionArrCount++;
                } else if (type != null && line.includes("/listOf")) {
                    line = parser.parseFromString(line, "application/xml");

                    let p = {};
                    p["type"] = type;
                    p["target"] = line.firstChild.localName;

                    let attr = line.firstChild.attributes;

                    for (let i = 0; i < attr.length; i++) {
                        p[attr[i].localName] = attr[i].value;
                    }

                    modelChanges.push(p);

                }

            });

            return modelChanges;
        },

        getModelAttr: function () {

            // gets Attributes on SBML tag
            let attr = [];
            let modelAttr = {};
            attr = this.getAllNodeAttr(this.newDocument.firstChild);
            modelAttr['sbmlAttr'] = {
                'attr': attr
            };

            //get all Attributes of model
            attr = this.getAllNodeAttr(this.newDocument.firstChild.children.item('model'));
            modelAttr['modelAttr'] = {
                'attr': attr
            };

            //get all childrenTags of <model>
            let lists = this.newDocument.firstChild.children.item(0).children;
            for (let i = 0; i < lists.length; i++) {
                modelAttr[lists[i].localName] = {
                    "attr": {}
                };

                //additional but most likely different data will be available for each list

                //modelAttr.push(p);
            }

            return modelAttr;
        },

        combineModelAttrWithChanges: function () {
            let modelData = this.getModelAttr();
            let changes = this.getModelChanges();

            //get units
            let unitsListPath = useGetLocalXPath("/sbml[1]/model[1]/listOfUnitDefinitions[1]");
            let units = this.getUnits(this.newDocument, unitsListPath);
            if (units) modelData["listOfUnitDefinitions"] = units;

            //get parameters
            let parameterListPath = useGetLocalXPath("/sbml[1]/model[1]/listOfParameters[1]");
            let parameters = this.getParameters(this.newDocument, parameterListPath);
            if (parameters) modelData["listOfParameters"] = parameters;

            //get rules
            let rulesListPath = useGetLocalXPath("/sbml[1]/model[1]/listOfRules[1]");
            let rules = this.getRules(this.newDocument, rulesListPath);
            if (rules) modelData["listOfRules"] = rules;

            //get compartments
            let compartmentsListPath = useGetLocalXPath("/sbml[1]/model[1]/listOfCompartments[1]");
            let compartments = this.getCompartments(this.newDocument, compartmentsListPath);
            if (compartments) modelData["listOfCompartments"] = compartments;

            //sort changes by sbml lists
            let unitChanges = [];
            let parameterChanges = [];
            let rulesChanges = [];
            let sbmlChanges = [];
            let modelChanges = [];
            let compartmentsChanges = [];

            changes.forEach((c) => {
                let path;

                let target;

                if (c.newPath === "/sbml[1]/model[1]") target = "modelAttr";
                else if (c.newPath === "/sbml[1]") target = "sbmlAttr";
                else if (c.newPath) path = c.newPath;
                else if (c.oldPath === "/sbml[1]/model[1]") target = "modelAttr";
                else if (c.oldPath === "/sbml[1]") target = "sbmlAttr";
                else path = c.oldPath;

                //console.debug(path);

                if (path != undefined) {
                    target = this.getList(path);
                }

                //console.debug(target);

                switch (target) {
                    case "listOfRules":
                        if (c.oldPath === "/sbml[1]/model[1]/listOfRules[1]") return;
                        //check whether a parentNode was already added, because math changes are missing the "triggeredBy" sometimes
                        for (let i = 0; i < rulesChanges.length; i++) {
                            if (c.oldParent.startsWith(rulesChanges[i].oldPath)) return;
                        }
                        console.log(c);

                        rulesChanges.push(c);
                        break;
                    case "listOfParameters":
                        parameterChanges.push(c);
                        break;
                    case "listOfUnitDefinitions":
                        unitChanges.push(c);
                        break;
                    case "modelAttr":
                        modelChanges.push(c);
                        break;
                    case "sbmlAttr":
                        sbmlChanges.push(c);
                        break;
                    case "listOfCompartments":
                        compartmentsChanges.push(c);
                        break;
                    default:
                        alert("no change list for: " + target);
                }

                //fill decision array
                this.decisionArr[c.id] = {
                    "decision": -1,
                    "type": c.type
                };
                this.decisionArrCount++;
                console.debug(this.decisionArr);
            })

            //connect changes to rules, go through the possible lists in sbml reuse as much code as possible

            //changes in model info
            modelChanges.forEach((c) => { //handle possible targets, call functions for reusable code
                //target: attribute
                if (c.target == "attribute") {
                    modelData = this.addAttributeChanges(c, modelData, "modelAttr");

                } else if (c.target == "node") { //TODO: target node
                    console.info(c);
                    alert("Node in modelChanges");
                } else console.error(c);
            })

            sbmlChanges.forEach((c) => { //handle possible targets, call functions for reusable code
                //target: attribute
                if (c.target == "attribute") {
                    modelData = this.addAttributeChanges(c, modelData, "sbmlAttr");

                } else if (c.target == "node") { //TODO: target node
                    modelData = this.changedNode(c, modelData, "sbmlAttr");
                    //alert("Node in sbmlChanges");
                }
            })

            parameterChanges.forEach((c) => { //handle possible targets, call functions for reusable code
                //target: attribute
                if (c.target == "attribute") {
                    modelData = this.addAttributeChanges(c, modelData, "listOfParameters");
                } else if (c.target == "node") { //TODO: target node
                    modelData = this.changedNode(c, modelData, "listOfParameters");
                } else console.error(c);
            })

            unitChanges.forEach((c) => { //handle possible targets, call functions for reusable code
                //target: attribute
                if (c.target == "attribute") {
                    modelData = this.addAttributeChanges(c, modelData, "listOfUnitDefinitions");

                } else if (c.target == "node") { //TODO: target node
                    modelData = this.changedNode(c, modelData, "listOfUnitDefinitions");
                } else console.error(c);
            })

            let addedNodes = {};

            rulesChanges.forEach((c) => { //handle possible targets, call functions for reusable code
                //target: attribute
                if (c.target == "attribute") {
                    modelData = this.addAttributeChanges(c, modelData, "listOfRules");
                } else if (c.target == "node" && c.oldPath !== "/sbml[1]/model[1]/listOfRules[1]") { //TODO: target node
                    modelData = this.changedNode(c, modelData, "listOfRules");
                } else console.error(c);
            })

            compartmentsChanges.forEach((c) => {
                if (c.target == "attribute") {
                    modelData = this.addAttributeChanges(c, modelData, "listOfCompartments");
                }
                if (c.target == "node") {
                    modelData = this.changedNode(c, modelData, "listOfCompartments");
                }
            })

            this.modelArr = modelData;
            console.debug(this.modelArr);

        },

        getAllNodeAttr: function (node) {
            let attrMap = node.attributes;
            let p = {};
            for (let i = 0; i < attrMap.length; i++) {
                p[attrMap[i].localName] = attrMap[i].value;
            }

            return p;
        },

        allFrom: function (version) {
            this.all = version;

            for (const [key] of Object.entries(this.decisionArr)) {
                this.updateDecision(key, version);
            }
        },

        updateDecision: function (changeID, d) {
            let h = this.decisionArr[changeID]['decision'];
            if (h != d) {
                if (h == -1) {
                    this.progressCount++;
                    this.progress = Math.round(100 / this.decisionArrCount * this.progressCount * 100) / 100;
                }
                this.decisionArr[changeID]['decision'] = d;
            }

        },

        addAttributeChanges: function (c, modelData, list) {

            let el = this.getAttributeChange(c); //?? eigentlich unnötig ??

            //if(list != "sbmlAttr" && list != "modelAttr") console.error("anderen Listen behandeln", list);

            if (c.type === "u") {
                if (list === "sbmlAttr" || list === "modelAttr") modelData[list].attr[el.name] = {
                    "changeID": el.changeID,
                    "oldValue": el.oldValue,
                    "newValue": el.newValue
                }; //TODO: anderen Listen behandeln! target= modelData[list].attr[el.name]; geht nicht da call-by-value
                else if (list === "listOfParameters" || list === "listOfCompartments" || list === "listOfUnitDefinitions") { //parameterupdate

                    let target = this.getChangeTarget(c.newPath);

                    let targetAttr = c.name;
                    modelData[list][target.childNo][targetAttr] = {
                        "type": "u",
                        "changeID": c.id,
                        "oldValue": c.oldValue,
                        "newValue": c.newValue
                    };

                } else {
                    console.debug(c, c.type === "d");
                    alert("attribute updated");
                }

            } else if (c.type === "i") { //elements are already contained in newDoc -> mark inserted elements and add changeID
                if (list === "sbmlAttr" || list === "modelAttr") modelData[list].attr[el.name] = {
                    "changeID": el.changeID,
                    "newValue": el.newValue
                };
                //elements are already contained in newDoc -> mark inserted elements
                else if (list === "listOfParameters" || list === "listOfCompartments" || list === "listOfUnitDefinitions") { //parameterupdate

                    let target = this.getChangeTarget(c.newPath);

                    let targetAttr = c.name;
                    modelData[list][target.childNo][targetAttr] = {
                        "type": "i",
                        "newValue": c.newValue
                    };

                } else {
                    console.debug(c, c.type === "d");
                    alert("attribute inserted");
                }

            } else if (c.type === "d") {
                if (list === "sbmlAttr" || list === "modelAttr") modelData[list].attr[el.name] = {
                    "changeID": el.changeID,
                    "oldValue": el.oldValue
                };
                //insert attribute at right element, mark attribute as deleted
                else if (list === "listOfParameters" || list === "listOfCompartments" || list === "listOfUnitDefinitions") {
                    console.debug(c, el, list);
                    //fthisalert("japp");
                    let target = this.getChangeTarget(c.oldPath);

                    let targetAttr = c.name;
                    modelData[list][target.childNo][targetAttr] = {
                        "type": "i",
                        "changeID": c.id,
                        "newValue": c.oldValue
                    };
                } else {
                    console.debug(c, c.type === "d");
                    alert("attribute deleted");
                }
            }

            return modelData;
        },

        changedNode: function (c, modelData, list) {
            //console.info(c.id, c);

            if (c.type == "i") {

                let childNo = c.newChildNo - 1;

                if (list === "sbmlAttr" || list === "modelAttr") {
                    modelData[list][childNo] = {};
                    modelData[list][childNo]["change"] = c.type;
                    modelData[list][childNo]["changeID"] = c.id;
                } else if (list === "listOfParameters" || list === "listOfUnitDefinitions" || list === "listOfCompartments") {
                    console.debug(modelData[list]);

                    //let target = this.getChangeTarget(c.newPath);
                    let h = modelData[list][childNo];
                    modelData[list][childNo] = {};
                    modelData[list][childNo]["change"] = c.type;
                    modelData[list][childNo]["changeID"] = c.id;
                    modelData[list][childNo]["attr"] = h;
                    console.debug(modelData[list][childNo]);
                }
            } else if (c.type = "d") {

                let n;

                if (list === "listOfParameters") n = this.getSingleParameter(this.oldDocument, c.oldPath);
                else if (list === "listOfUnitDefinitions") n = this.getSingleUnit(this.oldDocument, c.oldPath);
                else if (list === "listOfRules") {
                    n = this.getSingleRule(this.oldDocument, c.oldPath);
                } else {
                    alert("handle node deletetion pls! " + c.oldTag);
                }

                //check whether list exists
                if (modelData[list] === undefined) modelData[list] = [];

                // attach node to model data
                modelData[list].push({
                    "change": c.type,
                    "changeID": c.id,
                    "attr": n
                });
            }
            return modelData;
        },

        getUnits: function (doc, path) { //produces Array out of information from Unit list of a doc
            // name: , attr: [name: , oldV: , newV: ], math: [oldU, newU]
            let n = getNode(doc, path);
            let units = n.children;
            let unitList = [];

            for (let i = 0; i < units.length; i++) {
                let unit = {};

                //compute attribute list

                let attr = units[i].attributes;
                for (let j = 0; j < attr.length; j++) {
                    unit[attr[j].localName] = attr[j].value;
                }

                //compute presentation MathML for units

                let definitions = units.item(i).children[0].children;

                unit["math"] = this.getUnitMathML(definitions);

                unitList[i] = unit;
            }

            return unitList;

            ///////////////////////<-------------------------------------------------
        },

        getUnitMathML: function (definitions) {

            let times = " &InvisibleTimes; ";
            let numerator = null;
            let denominator = null;

            for (let j = 0; j < definitions.length; j++) {
                //unit["childNumber"] = j;  due to the transformation to mathML, i can't fix the elemnts to the path

                let exp = Number(definitions[j].attributes.exponent.value);
                let s = this.getScaleSymbol(definitions[j].attributes.scale);
                let m = definitions[j].attributes.multiplier.value;
                let u = this.getUnit(definitions[j].attributes.kind)

                let e = "";
                if (m && m != 1) e = m;
                if (Math.abs(exp) != 1) e += "<msup>" + s + u + " " + Math.abs(exp) + "</msup>"
                else e += s + u;

                if (exp > 0) {
                    if (numerator == null && e != null) numerator = "<mrow>" + "<mi mathvariant='italic'>" + e + "</mi>";
                    else numerator += times + "<mi mathvariant='italic'>" + e + "</mi>";
                } else if (exp < 0) {
                    if (denominator == null && e != null) denominator = "<mrow>" + "<mi mathvariant='italic'>" + e + "</mi>";
                    else denominator += times + "<mi mathvariant='italic'>" + e + "</mi>";
                } else console.error("exponent zero");

            }

            numerator += "</mrow>";
            if (denominator != null) denominator += "</mrow>";

            if (denominator != null) return '<math xmlns="http://www.w3.org/1998/Math/MathML" display="block"> ' + "<mfrac>" + numerator + denominator + "</mfrac></math>";
            else return '<math xmlns="http://www.w3.org/1998/Math/MathML">' + numerator + "</math>";
        },

        getRules: function (doc, path, changeType) {

            let n = getNode(doc, path);
            if (n == null) return;
            let rules = n.children;
            let rulesList = [];
            for (let i = 0; i < rules.length; i++) {

                //console.debug(rules[i]);

                let rule = {};
                if (changeType) rule["change"] = changeType;

                let type = rules[i].localName;

                //compute attribute list
                rule["type"] = type;
                //rule["changeID"] = type;

                rule["attr"] = {};
                let attr = rules[i].attributes;

                for (let j = 0; j < attr.length; j++) {
                    rule["attr"][attr[j].localName] = attr[j].value;
                }

                let childs = rules[i].children;
                for (let j = 0; j < childs.length; j++) {
                    if (childs[j].localName == "math") {
                        rule["attr"]["math"] = childs[j].outerHTML;
                    }
                }

                if (type === "assignmentRule") {
                    //alert("chip");
                } else if (type === "algebraicRule") {
                    // alert("chap");

                } else if (type === "rateRule") {
                    // alert("chip&chap");

                } else {
                    console.error(type + " unexpected type of Rule");
                }

                rulesList[i] = rule;

            }

            return rulesList;

        },

        getCompartments: function (doc, path) { // produces array of 
            let n = getNode(doc, path);
            let compartments = n.children;
            let compartmentList = [];

            console.debug(compartments);

            for (let i = 0; i < compartments.length; i++) {
                let comp = {};

                for (let a = 0; a < compartments[i].attributes.length; a++) {
                    comp[compartments[i].attributes[a].name] = compartments[i].attributes[a].value;
                }

                compartmentList[i] = comp;
            }

            return compartmentList;
        },

        getAttributeChange: function (change) {
            let el = {};
            el["target"] = change.target;
            el["name"] = change.name;

            if (change.type == "u") {
                el["changeID"] = change.id;
                el["changeType"] = "u";
                el["oldValue"] = change.oldValue;
                el["newValue"] = change.newValue;
            } else if (change.type == "i") {
                el["changeID"] = change.id;
                el["changeType"] = "i";
                el["newValue"] = change.newValue;
            } else if (change.type == "d") {
                el["changeID"] = change.id;
                el["changeType"] = "d";
                el["oldValue"] = change.oldValue;
            }

            return el;
        },

        getChangeTarget: function (path) {
            //target = modelData[this.getList(path)];

            let leaf = path.slice(path.lastIndexOf('/') + 1);
            let childNo = (leaf.slice(leaf.indexOf('[') + 1, leaf.lastIndexOf(']')));
            childNo = Number(childNo) - 1;
            let leafTag = leaf.substring(0, leaf.indexOf('['));

            return {
                "tag": leafTag,
                "childNo": childNo
            };
        },

        getParameters: function (doc, path) {

            let parameters = getNode(doc, path).children;
            let parameterList = [];

            for (let i = 0; i < parameters.length; i++) {
                parameterList[i] = {};
                let attr = parameters[i].attributes;

                for (let j = 0; j < attr.length; j++) {
                    parameterList[i][attr[j].localName] = attr[j].value;
                }
            }
            return parameterList;
        },

        getSingleParameter: function (doc, path) {

            //
            path = useGetLocalXPath(path)
            let parameterNode = getNode(doc, path);
            let parameter = {};

            let attr = parameterNode.attributes;

            for (let j = 0; j < attr.length; j++) {
                parameter[attr[j].localName] = attr[j].value;
            }

            return parameter;
        },

        getSingleRule: function (doc, path) { //same structure as unit but not for math 
            path = useGetLocalXPath(path);
            let ruleNode = getNode(doc, path);

            let rule = {};

            let attr = ruleNode.attributes;
            for (let j = 0; j < attr.length; j++) {
                rule[attr[j].localName] = attr[j].value;
            }

            console.debug(ruleNode.children[0]);

            rule["math"] = ruleNode.children[0].outerHTML;
            //console.debug(path, rule, ruleNode);

            return rule;
        },

        getSingleUnit: function (doc, path) {
            path = useGetLocalXPath(path);
            let unitNode = getNode(doc, path);

            let unit = {};

            let attr = unitNode.attributes;
            for (let j = 0; j < attr.length; j++) {
                unit[attr[j].localName] = attr[j].value;
            }

            //compute presentation MathML for units

            let definitions = unitNode.children[0].children;
            unit["math"] = this.getUnitMathML(definitions);

            return unit;

        },

        getScaleSymbol: function (s) {
            s = Number(s.value);
            switch (s) {
                case NaN:
                    return '';
                case 24:
                    return 'Y';
                case 21:
                    return 'Z';
                case 18:
                    return 'E';
                case 15:
                    return 'P';
                case 12:
                    return 'T';
                case 9:
                    return 'G';
                case 6:
                    return 'M';
                case 3:
                    return 'k';
                case 2:
                    return 'h';
                case 1:
                    return 'da';
                case 0:
                    return '';
                case -1:
                    return 'd';
                case -2:
                    return 'c';
                case -3:
                    return 'm';
                case -6:
                    return 'µ';
                case -9:
                    return 'n';
                case -12:
                    return 'p';
                case -15:
                    return 'f';
                case -18:
                    return 'a';
                case -21:
                    return 'z';
                case -24:
                    return 'y';
                default:
                    return 'missing scale';
            }
        },

        getUnit: function (u) {
            u = u.value;
            switch (u) {
                case "":
                    return '';
                case "meter":
                    return 'm';
                case "litre":
                    return 'l';
                case "kilogram":
                    return 'kg';
                case "gram":
                    return 'g';
                case "second":
                    return 's';
                case "ampere":
                    return 'A';
                case "kelvin":
                    return 'K';
                case "celsius":
                    return '°C';
                case "mole":
                    return 'mol';
                case "candela":
                    return 'cd';
                case "hertz":
                    return 'Hz';
                case "newton":
                    return 'N';
                case "candela":
                    return 'cd';
                case "joule":
                    return 'J';
                default:
                    return 'missing unit';
            }
        },

        getList: function (path) { //list from path
            let regex = new RegExp('listOf[^\[]*', 'g');
            let r = regex.exec(path);
            return r[0];
        }

    },
    watch: {
        file1: function () {
            this.checkForm();
        },
        file2: function () {
            this.checkForm();
        }
    },
    async mounted() {

        //check for dev modee
        if (this.dev == 1) {

            const promiseDiff = await axios.get('/dev/fake-dupreez/6-7f-xmlDiff.xml');

            const promiseV1 = await axios.get('/dev/fake-dupreez/dupreez6.xml');
            const promiseV2 = await axios.get('/dev/fake-dupreez/dupreez7-f.xml');
            const promiseJson = await axios.get('/dev/fake-dupreez/6-7f-sbgnJson.json');

            this.createInterfaceLocally(promiseV1, promiseV2, promiseDiff, promiseJson);
        }

    },
    updated() {
        if (this.view === "units" || this.view === "rules") {
            MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
        }
    },
};
</script>

<style>
.bivesGraph {
    min-height: 300px;
    background-color: lightgray;
}

/*bives-colors*/
.delete-color {
    color: #d66a56;
}

.insert-color {
    color: #76d6af;
}

.move-color {
    color: #8e67d6;
}

.update-color {
    color: #d6d287;
}
</style>
