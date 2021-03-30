import React from 'react';
import '../assets/styles/Type.scss';

interface TypeEfficacyProps {
    efficacy: number
}

const efficacyDisplayMap: Record<number, string> = {
    0: '0',
    25: '¼',
    50: '½',
    100: '1',
    200: '2',
    400: '4',
};

export default function TypeEfficacy(props: TypeEfficacyProps) {
    return (
        <span className={`pkt-type-efficacy pkt-type-efficacy-${props.efficacy}`}>
            {efficacyDisplayMap[props.efficacy]}
        </span>
    );
}
