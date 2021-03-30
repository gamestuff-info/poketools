import EncounterConditionLabel from './EncounterConditionLabel';
import EncounterNote from './EncounterNote';
import React from 'react';

export default function EncounterConditionList(props: { encounter: ApiRecord.Pokemon.Encounter }) {
    const {encounter} = props;
    return (
        <ul className="list-inline">
            {encounter.conditions.map((condition: ApiRecord.Pokemon.EncounterConditionState) => (
                <li className="list-inline-item" key={condition['@id']}>
                    <EncounterConditionLabel condition={condition}/>
                </li>
            ))}
            {encounter.note && (<EncounterNote encounter={encounter}/>)}
        </ul>
    );
}
