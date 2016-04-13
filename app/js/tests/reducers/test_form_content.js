'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	formContent = require( '../../lib/reducers/form_content' );

test( 'SELECT_AMOUNT sets amount and isCustomAmount', function ( t ) {
	var stateBefore = { amount: 99, isCustomAmount: true },
		expectedState = { amount: 5, isCustomAmount: false };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_AMOUNT', payload: { amount: 5 } } ), expectedState );
	t.end();
} );

test( 'SELECT_AMOUNT keeps amount if selected amount is null', function ( t ) {
	var stateBefore = { amount: 99, isCustomAmount: true },
		expectedState = { amount: 99, isCustomAmount: false };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_AMOUNT', payload: { amount: null } } ), expectedState );
	t.end();
} );

test( 'INPUT_AMOUNT sets amount and isCustomAount', function ( t ) {
	var stateBefore = { amount: 5, isCustomAmount: false },
		expectedState = { amount: '42.23', isCustomAmount: true };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'INPUT_AMOUNT', payload: { amount: '42.23' } } ), expectedState );
	t.end();
} );

test( 'CHANGE_CONTENT changes the field', function ( t ) {
	var stateBefore = { paymentType: 'PPL', amount: 0 },
		expectedState = { paymentType: 'BEZ', amount: 0 },
		action = { type: 'CHANGE_CONTENT', payload: { value: 'BEZ', contentName: 'paymentType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'CHANGE_CONTENT throws an error if the field name is not allowed', function ( t ) {
	var action = { type: 'CHANGE_CONTENT', payload: { value: 'supercalifragilistic', contentName: 'unknownField' } };

	t.throws( function () {
		formContent( {}, action );
	} );
	t.end();
} );

test( 'When CHANGE_CONTENT sets address type to private, company name is cleared', function ( t ) {
	var stateBefore = { companyName: 'Globex Corp', addressType: 'firma' },
		expectedState = { companyName: '', addressType: 'privat' },
		action = { type: 'CHANGE_CONTENT', payload: { value: 'privat', contentName: 'addressType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'When CHANGE_CONTENT sets address type to company, names are cleared', function ( t ) {
	var stateBefore = { personalTitle: 'Dr.', firstName: 'Hank', lastName: 'Scorpio', addressType: 'privat' },
		expectedState = { personalTitle: '', firstName: '', lastName: '', addressType: 'firma' },
		action = { type: 'CHANGE_CONTENT', payload: { value: 'firma', contentName: 'addressType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'When CHANGE_CONTENT sets address type to anonymous, all personal data fields are cleared', function ( t ) {
	var stateBefore = {
			companyName: '',
			personalTitle: 'Dr.',
			firstName: 'Hank',
			lastName: 'Scorpio',
			street: 'Hammock District',
			postCode: '12345',
			city: 'Cypress Creek',
			addressType: 'privat',
			email: 'hank@globex.com'
		},
		expectedState = {
			companyName: '',
			personalTitle: '',
			firstName: '',
			lastName: '',
			street: '',
			postCode: '',
			city: '',
			addressType: 'anonym',
			email: ''
		},
		action = { type: 'CHANGE_CONTENT', payload: { value: 'anonym', contentName: 'addressType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'INITIALIZE_CONTENT changes multiple fields', function ( t ) {
	var stateBefore = { paymentType: 'PPL', amount: 0, recurringPayment: 0 },
		expectedState = { paymentType: 'BEZ', amount: '25,00', recurringPayment: 0 },
		action = { type: 'INITIALIZE_CONTENT', payload: { amount: '25,00', paymentType: 'BEZ' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'INITIALIZE_CONTENT throws an error if a field name is not allowed', function ( t ) {
	var action = { type: 'INITIALIZE_CONTENT', payload: {
		amount: '25,00',
		paymentType: 'BEZ',
		unknownField: 'supercalifragilistic'
	} };

	t.throws( function () {
		formContent( {}, action );
	}, /unknownField/ );
	t.end();
} );

