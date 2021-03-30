import '../../../assets/styles/Gauge.scss';
import {sum} from 'mathjs';

interface LinearGaugeParams extends Record<string, any> {
    value: number | Array<number>;
}

export default function LinearGauge(props: LinearGaugeParams) {
    const classNames = props.className ? props.className.split(' ') : [];
    classNames.unshift('pkt-gauge-linear');

    let values = props.value;
    if (!Array.isArray(values)) {
        values = [values];
    }

    // Assume a percentage if only one value passed.
    const total = values.length > 1 ? sum(...values) : 100;

    // Calculate percentages
    let previousPercentage = 0;
    const rects = values.map((value, index) => {
        const percentage = (value / total) * 100;
        const rect = (
            <rect key={index}
                  y="0"
                  x={previousPercentage}
                  width={percentage}
                  className={`pkt-gauge-value-${index}`}/>
        );
        previousPercentage += percentage;
        return rect;
    });

    return (
        <svg xmlns="http://www.w3.org/2000/svg"
             id="svg8"
             version="1.1"
             className={classNames.join(' ')}
             width="100"
             aria-hidden="true"
        >
            <g>
                <rect className="pkt-gauge-empty" y="0" x="0" width="100"/>
                {rects}
            </g>
        </svg>
    );
}
