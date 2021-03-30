import {OverlayTrigger, Tooltip} from 'react-bootstrap';
import React from 'react';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import EntityLabel from '../common/components/EntityLabel';
import '../assets/styles/EncounterConditionLabel.scss';

export default function EncounterConditionLabel(props: { condition: ApiRecord.Pokemon.EncounterConditionState }) {
    const {condition} = props;
    return (
        <OverlayTrigger overlay={
            <Tooltip id={`encounter-condition-${condition.slug}`}>
                {condition.name}
            </Tooltip>
        } placement="auto">
            <EntityLabel>
                <EntityLabel.Icon
                    className="pkt-icon-encountercondition"
                    src={getAssetUrl(`encounter_condition_state/${condition.condition.slug}_${condition.slug}.svg`, AssetPackage.MEDIA)}
                    alt={condition.name}
                />
            </EntityLabel>
        </OverlayTrigger>
    );
}
