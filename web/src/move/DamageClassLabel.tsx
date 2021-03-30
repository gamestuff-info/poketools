import React from 'react';
import {OverlayTrigger, Tooltip} from 'react-bootstrap';

interface DamageClassEmblemProps {
    damageClass: ApiRecord.Move.DamageClass
}

export default function DamageClassLabel(props: DamageClassEmblemProps) {
    const {damageClass} = props;
    return (
        <OverlayTrigger overlay={
            <Tooltip id={`damage-class-${damageClass.slug}`}>{damageClass.name}</Tooltip>
        } placement="auto">
            <span>
                <i className={`pkt-icon pkt-icon-damageclass-${damageClass.slug}`}/>
                <span className="sr-only">{damageClass.name} class</span>
            </span>
        </OverlayTrigger>
    );
}
